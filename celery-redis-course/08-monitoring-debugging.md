# Lesson 08 — Monitoring & debugging

Background tasks are invisible by nature — they run somewhere else, later. So you
need tools to *see* what's happening. This lesson is about visibility.

## Reading worker logs (your first tool)

The worker terminal already tells you a lot. Run it with `--loglevel=info` and
you'll see, per task:

```
Task tasks_app.tasks.add[3f7c...] received
Task tasks_app.tasks.add[3f7c...] succeeded in 0.002s: 10
```

On failure:
```
Task ...charge[ab12...] raised: CardDeclinedError('insufficient funds')
Traceback (most recent call last): ...
```

For deeper detail while debugging, use `--loglevel=debug`. In production, log to
files / a log aggregator, not just the terminal.

## Flower — the web dashboard

[Flower](https://flower.readthedocs.io/) is a real-time web UI for Celery. It's
the single best tool for *seeing* your task system.

```bash
pip install flower
celery -A config flower --port=5555
```

Open `http://localhost:5555`. You get:
- **Workers**: which are online, how many tasks each is processing.
- **Tasks**: live feed of received / started / succeeded / failed, with args,
  runtime, results, and tracebacks.
- **Broker**: queue lengths (how many tasks are waiting).
- Ability to **inspect** and even **revoke** (cancel) tasks.

Spend 10 minutes clicking around Flower while firing tasks — it makes the whole
mental model concrete.

> Protect Flower in production (it exposes task data and controls). Put it behind
> auth / a VPN — `--basic-auth=user:pass` at minimum.

## Inspecting workers from code

Celery's `inspect` API lets you query live worker state:

```python
from config.celery import app

i = app.control.inspect()

i.active()       # tasks currently running, per worker
i.scheduled()    # tasks with an eta/countdown waiting to run
i.reserved()     # tasks fetched by a worker but not started yet
i.stats()        # worker stats (pool size, etc.)
i.registered()   # task names each worker knows about
```

From the CLI:
```bash
celery -A config inspect active
celery -A config inspect ping        # are workers responding?
celery -A config status              # cluster overview
```

## Checking the queue directly in Redis

Since Redis is the broker, you can peek at it:

```bash
redis-cli
> LLEN celery          # how many tasks waiting in the default queue
> KEYS *               # see broker/result keys (don't do this on huge prod DBs)
```

A growing `LLEN` means tasks are arriving faster than workers can process them —
time to add workers or speed up tasks.

## Cancelling / revoking tasks

```python
from config.celery import app
app.control.revoke("task-id-here")               # don't run it (if not started)
app.control.revoke("task-id-here", terminate=True)  # kill it if already running
```

## Common bugs and how to diagnose them

| Symptom | Likely cause | How to check / fix |
|---------|-------------|--------------------|
| Task "queued" but never runs | No worker running, or wrong broker URL | `celery -A config inspect ping`; start worker |
| Code change has no effect | Worker still running old code | **Restart the worker** |
| Worker doesn't know the task | Task not in `tasks.py` / app not installed / not autodiscovered | Check `inspect registered`; restart worker |
| `result.get()` hangs forever | No result backend, or task never ran | Configure backend; verify worker is up |
| Status always `PENDING` | `ignore_result`, no backend, or bad task id | Enable backend; verify the id |
| Tasks pile up (queue grows) | Too few workers / tasks too slow | Add workers/concurrency; optimize task |
| Task runs twice | At-least-once delivery / retries | Make it idempotent (Lesson 05) |
| `Received unregistered task` | Worker imported different code than producer | Same codebase/version on web + worker; restart |

## Auto-reload in development

Restarting the worker on every change gets old. For dev, use `watchdog` to
auto-restart on file changes:

```bash
pip install watchdog
watchmedo auto-restart --directory=./ --pattern="*.py" --recursive -- \
    celery -A config worker --loglevel=info
```

Now editing a task restarts the worker automatically. (Don't use this in prod.)

## Logging inside tasks

Use Celery's task logger so messages are tagged with the task name/id:

```python
from celery.utils.log import get_task_logger

logger = get_task_logger(__name__)

@shared_task
def process(order_id):
    logger.info("Processing order %s", order_id)
    ...
    logger.info("Done with order %s", order_id)
```

## Errors → alerting (Sentry)

In production, route task exceptions to Sentry (or similar). The Sentry SDK has
a Celery integration that captures task failures automatically:

```python
import sentry_sdk
from sentry_sdk.integrations.celery import CeleryIntegration
sentry_sdk.init(dsn="...", integrations=[CeleryIntegration()])
```

## Exercise

1. Install and run Flower. Fire a `slow_square` and watch it move through
   `RECEIVED → STARTED → SUCCESS` live.
2. Make a task fail (raise an exception) and find its traceback in Flower.
3. From the shell, run `app.control.inspect().active()` while a `slow_square`
   (with a longer sleep) is running, and see it listed.
4. Set up `watchmedo` auto-restart and confirm editing a task reloads the worker.

Next → [Lesson 09: Production checklist & gotchas](09-production-and-gotchas.md)
