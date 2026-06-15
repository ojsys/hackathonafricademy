# Lesson 06 — Scheduling with Celery Beat

So far we *fire* tasks from code. But lots of work should run **automatically on
a schedule**: nightly reports, hourly cleanup, weekly digests. That's **Celery
Beat**.

## What Beat is

Beat is a separate **scheduler** process. It doesn't run tasks itself — it just
*sends* them to the queue at the right times, and your normal workers run them.

```
 ┌────────────┐  "it's 2am, queue nightly_report"  ┌─────────┐  ┌─────────┐
 │ Celery Beat │ ──────────────────────────────────►│  Redis  │─►│ Worker  │
 │ (scheduler) │                                     │ (queue) │  │ (runs)  │
 └────────────┘                                     └─────────┘  └─────────┘
```

> You run **Beat** *and* a **worker**. Beat alone queues tasks but nothing runs
> them; a worker alone never gets scheduled tasks. You need both.

## Option A — schedule in code (`beat_schedule`)

Good for fixed schedules that live with your code. Add to `config/settings.py`:

```python
from celery.schedules import crontab

CELERY_BEAT_SCHEDULE = {
    # run every 30 seconds (great for testing)
    "heartbeat-every-30s": {
        "task": "tasks_app.tasks.heartbeat",
        "schedule": 30.0,                 # seconds (float)
    },
    # run every day at 02:00
    "nightly-report": {
        "task": "tasks_app.tasks.generate_daily_report",
        "schedule": crontab(hour=2, minute=0),
    },
    # run every Monday at 08:30
    "weekly-digest": {
        "task": "tasks_app.tasks.send_weekly_digest",
        "schedule": crontab(hour=8, minute=30, day_of_week="mon"),
        "args": (),                       # positional args for the task
        "kwargs": {},                     # keyword args
    },
    # run every 15 minutes
    "cleanup": {
        "task": "tasks_app.tasks.cleanup_temp_files",
        "schedule": crontab(minute="*/15"),
    },
}
```

Add the tasks in `tasks_app/tasks.py`:

```python
@shared_task
def heartbeat():
    print("💓 beat is alive")

@shared_task
def generate_daily_report():
    ...

@shared_task
def cleanup_temp_files():
    ...
```

## `crontab()` cheat sheet

```python
crontab()                                  # every minute
crontab(minute=0)                          # top of every hour
crontab(minute="*/15")                     # every 15 minutes
crontab(hour=2, minute=0)                  # daily at 02:00
crontab(hour="*/3")                        # every 3 hours
crontab(day_of_week="mon", hour=8, minute=30)   # Mondays 08:30
crontab(day_of_month=1, hour=0, minute=0)       # 1st of month, midnight
```

Times follow `CELERY_TIMEZONE`. Set it correctly (e.g. `"Africa/Lagos"`) or
your "2am" job runs at the wrong local time.

## Run Beat

In a **new terminal** (you now have Redis + worker + beat, plus runserver):

```bash
celery -A config beat --loglevel=info
```

With the 30-second heartbeat above, watch your **worker** terminal — every 30s
it receives and runs `heartbeat`, printing `💓 beat is alive`. That confirms the
whole schedule pipeline works.

> **Run only ONE beat process.** Two beat processes = every scheduled task fires
> twice. Workers you can scale freely; beat must be a singleton.

### Dev shortcut: combine worker + beat
For local dev only, you can embed beat in the worker:
```bash
celery -A config worker --beat --loglevel=info
```
Don't do this in production — keep them separate so you can scale and restart
workers without disturbing the schedule.

## Option B — manage schedules in the Django admin (django-celery-beat)

Hard-coding schedules means a code deploy to change them. For schedules that
non-developers tweak, use **django-celery-beat**, which stores them in the DB and
exposes them in the Django admin.

```bash
pip install django-celery-beat
```
```python
# settings.py
INSTALLED_APPS += ["django_celery_beat"]
```
```bash
python manage.py migrate
```

Then run beat with the database scheduler:
```bash
celery -A config beat -l info --scheduler django_celery_beat.schedulers:DatabaseScheduler
```

Now go to the Django admin → **Periodic Tasks**. You can add/edit/disable
schedules through the UI, no deploy needed. You define interval/crontab schedules
and attach tasks to them.

**Which to choose?**
- Schedule is fixed and developer-owned → **Option A** (in code). Simpler.
- Schedule changes often or non-devs manage it → **Option B** (admin/DB).

## Gotchas

- **Beat not running** → scheduled tasks simply never appear. Check the beat
  process is alive.
- **Two beats** → duplicate runs. Keep it a singleton.
- **Wrong timezone** → jobs at unexpected hours. Set `CELERY_TIMEZONE`.
- **Long task overruns its interval** → e.g. a 20-min task scheduled every 15
  min can pile up. Make such tasks idempotent and/or guard against overlap
  (a Redis lock — see Lesson 09).
- Changing `CELERY_BEAT_SCHEDULE` → **restart beat** to pick it up.

## Exercise

1. Add a `heartbeat` task on a 20-second schedule. Run beat + worker and confirm
   it prints every 20s.
2. Add a `cleanup_temp_files` task scheduled every 5 minutes with `crontab`.
3. Install `django-celery-beat`, migrate, and create the same cleanup schedule
   from the **admin** instead. Disable the code one. Confirm it still fires.

Next → [Lesson 07: Workflows — chains, groups, chords](07-workflows.md)
