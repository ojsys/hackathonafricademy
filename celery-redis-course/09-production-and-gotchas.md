# Lesson 09 — Production checklist & gotchas

You can build tasks now. This lesson is what stands between "works on my laptop"
and "runs reliably in production." Skim it now; return to it before you deploy.

## Running workers in production

You don't run `celery worker` in a terminal forever. Use a **process manager**
so it restarts on crash/reboot:

- **systemd** (common on Linux VMs): a `celery.service` unit + `celerybeat.service`.
- **Supervisor**: classic option, simple config.
- **Docker / Kubernetes**: worker and beat as separate containers/deployments.

You run, at minimum, **three** long-lived processes:
1. Web server (gunicorn/uvicorn + Django)
2. Celery **worker(s)**
3. Celery **beat** (only if you use scheduling) — **exactly one** instance

Example systemd worker unit (sketch):
```ini
[Unit]
Description=Celery Worker
After=network.target redis.service

[Service]
WorkingDirectory=/srv/app
ExecStart=/srv/app/.venv/bin/celery -A config worker -l info --concurrency=4
Restart=always

[Install]
WantedBy=multi-user.target
```

## Concurrency & pools

```bash
celery -A config worker --concurrency=8           # 8 worker processes (prefork)
```

- **prefork** (default): multiple processes. Best for **CPU-bound** and general
  use. Set `--concurrency` near your CPU core count.
- **gevent/eventlet**: many green threads in one process. Best for **I/O-bound**
  tasks (lots of waiting on network) — e.g. `--pool=gevent --concurrency=200`.
- Rule of thumb: CPU-heavy → prefork; lots of API/network waiting → gevent.

Scale out by running **more worker processes/machines**, all pointing at the
same Redis broker.

## Use multiple queues (don't let slow tasks block fast ones)

By default everything shares one queue. A flood of slow report jobs can starve
your quick email tasks. Split them:

```python
# settings.py
CELERY_TASK_ROUTES = {
    "tasks_app.tasks.send_email":      {"queue": "fast"},
    "tasks_app.tasks.generate_report": {"queue": "slow"},
}
```

Run dedicated workers per queue:
```bash
celery -A config worker -Q fast -c 8 -n fast@%h
celery -A config worker -Q slow -c 2 -n slow@%h
```

Now report jobs can't delay confirmation emails. This is one of the highest-value
production patterns.

## Prefetch tuning

A worker grabs several tasks at once (`prefetch`). For **long** tasks this is bad
— one worker hoards tasks while others sit idle. For long-running tasks set:

```python
CELERY_WORKER_PREFETCH_MULTIPLIER = 1   # take one at a time
CELERY_TASK_ACKS_LATE = True            # ack after completion (with idempotency!)
```

For tons of tiny fast tasks, a higher prefetch is more efficient. Tune to your
workload.

## Preventing overlapping runs (Redis lock)

For a scheduled task that must never run twice concurrently, use a Redis lock:

```python
from django.core.cache import cache   # configured to use Redis

@shared_task
def nightly_sync():
    lock_id = "lock:nightly_sync"
    # acquire lock, auto-expire after 10 min so a crash can't deadlock it
    if not cache.add(lock_id, "1", timeout=600):
        return "already running, skipping"
    try:
        do_the_work()
    finally:
        cache.delete(lock_id)
```

## Result backend hygiene

- Don't store results you never read — set `task_ignore_result=True` globally or
  `ignore_result=True` per task.
- Results expire after `CELERY_RESULT_EXPIRES` (default 1 day). Keep it sane so
  Redis doesn't fill up:
  ```python
  CELERY_RESULT_EXPIRES = 3600   # seconds
  ```

## Redis as broker — durability notes

- Redis is fast but, in default config, **in-memory**. If Redis restarts and
  isn't persisting, **queued (not-yet-run) tasks can be lost.** Enable Redis
  persistence (AOF) for important queues, or use RabbitMQ for stronger
  guarantees.
- Use **separate Redis databases/instances** for broker vs. cache vs. results so
  a `FLUSHDB` on your cache doesn't nuke your task queue.
- Set `CELERY_BROKER_TRANSPORT_OPTIONS = {"visibility_timeout": ...}` if you have
  long ETA/countdown tasks (longer than the default visibility timeout), or they
  can be redelivered early.

## Security & config

- Keep `CELERY_ACCEPT_CONTENT = ["json"]`. **Never** enable the `pickle`
  serializer on an untrusted broker — it allows arbitrary code execution.
- Put secrets (broker URL with password) in env vars, not in code.
- Lock down Redis (bind to localhost / private network, require a password,
  don't expose 6379 to the internet).
- Protect Flower with auth.

## Pre-deploy checklist

- [ ] Worker(s) run under systemd/supervisor/k8s with `Restart=always`.
- [ ] Exactly **one** beat instance (if scheduling).
- [ ] Broker URL & backend come from env vars/secrets.
- [ ] `CELERY_ACCEPT_CONTENT = ["json"]` (no pickle).
- [ ] Tasks pass **ids, not objects**, and re-fetch inside.
- [ ] Critical tasks are **idempotent** and use `acks_late`.
- [ ] Time limits set (`soft_time_limit`/`time_limit`).
- [ ] Retries configured with backoff for external calls.
- [ ] Separate queues for fast vs. slow work.
- [ ] Result backend expiry set; unused results ignored.
- [ ] Monitoring in place (Flower + log aggregation + Sentry).
- [ ] Redis persistence considered for important queues.
- [ ] Deploy strategy restarts workers so they pick up new code.

## Exercise

1. Add `CELERY_TASK_ROUTES` to send `slow_square` to a `slow` queue and `add` to
   a `fast` queue. Start two workers (`-Q slow`, `-Q fast`) and confirm via the
   worker logs that each task lands on the right worker.
2. Add a Redis lock to a task and prove that a second concurrent call returns
   "already running."
3. Write a systemd (or Supervisor, or Docker Compose) config that runs your
   worker — even if you don't deploy it, get it written.

Next → [Lesson 10: Capstone project](10-capstone-project.md)
