# Lesson 03 — Your first task

Now we write a *real* task in an app and call it. We'll start simple, then make
it useful.

## `@shared_task` vs `@app.task`

You'll see two ways to define tasks:

- `@app.task` — uses a specific Celery app instance. Fine in `celery.py`.
- `@shared_task` — **the one you should use inside Django apps.** It doesn't
  depend on importing the Celery app, so it's reusable and avoids circular
  imports. Use this in your apps' `tasks.py`.

## Step 1 — Write a task

Create `tasks_app/tasks.py`:

```python
import time
from celery import shared_task


@shared_task
def add(x, y):
    """A trivial task so we can see the round-trip."""
    return x + y


@shared_task
def slow_square(n):
    """Pretend this is heavy work (e.g. image processing)."""
    time.sleep(3)          # simulate slow work
    return n * n
```

Restart your worker so it picks up the new tasks (workers don't hot-reload code
by default). Stop it with `Ctrl+C` and start it again:

```bash
celery -A config worker --loglevel=info
```

In the `[tasks]` section of the banner you should now see:
```
[tasks]
  . tasks_app.tasks.add
  . tasks_app.tasks.slow_square
```

> **Remember:** every time you change task code, **restart the worker.** This is
> the #1 "why isn't my change working?!" gotcha. (See `--autoreload` /
> `watchdog` tricks in Lesson 08 for dev convenience.)

## Step 2 — Call it from the shell

```bash
python manage.py shell
```
```python
from tasks_app.tasks import add

# Call it asynchronously — this returns IMMEDIATELY with a handle.
result = add.delay(4, 6)

result            # <AsyncResult: 3f7c...-...>
result.id         # the task id (a uuid string)
result.ready()    # False until the worker finishes
result.get()      # 10  (blocks until done — see warning below)
```

Watch the worker terminal: you'll see it receive `add`, run it, and report
`succeeded ... result: 10`.

### ⚠️ `result.get()` blocks — be careful

`.get()` waits for the task to finish. That's fine in the shell to learn, but
**never call `.get()` inside a Django view** — you'd be back to making the user
wait, defeating the entire point. (It can also deadlock if a task calls `.get()`
on another task.) In real code you usually fire the task and move on, or poll
`result.ready()` / check status later.

## Step 3 — Call a task from a view

Let's do the realistic version. In `tasks_app/views.py`:

```python
from django.http import JsonResponse
from .tasks import slow_square


def kick_off(request, n):
    # Fire the task — returns instantly, does NOT wait for the result.
    task = slow_square.delay(n)
    return JsonResponse({"task_id": task.id, "status": "queued"})


def check_status(request, task_id):
    from celery.result import AsyncResult
    res = AsyncResult(task_id)
    return JsonResponse({
        "task_id": task_id,
        "status": res.status,           # PENDING / STARTED / SUCCESS / FAILURE
        "result": res.result if res.ready() else None,
    })
```

Wire up `config/urls.py`:

```python
from django.urls import path
from tasks_app import views

urlpatterns = [
    path("run/<int:n>/", views.kick_off),
    path("status/<str:task_id>/", views.check_status),
]
```

Now run all three processes:

```bash
# terminal 1: Redis (already running)
# terminal 2:
celery -A config worker --loglevel=info
# terminal 3:
python manage.py runserver
```

Try it:
1. Visit `http://localhost:8000/run/5/` → instantly returns a `task_id`. ✅
   (No 3-second wait, even though the task sleeps 3 seconds.)
2. Copy the id, visit `http://localhost:8000/status/<task_id>/`.
   - Right away: `"status": "STARTED"` (or `PENDING`), `"result": null`.
   - After ~3 seconds, refresh: `"status": "SUCCESS"`, `"result": 25`. 🎉

You just built the fundamental async pattern: **kick off → return immediately →
poll for status/result.**

## What "PENDING" really means (gotcha)

`PENDING` doesn't only mean "queued and waiting." Celery returns `PENDING` for
**any task id it doesn't know about** — including typos and ids whose results
expired. It's the default "I have no information" state. Don't treat `PENDING`
as a guarantee the task exists.

## Exercise

1. Add a `@shared_task` called `reverse_string(text)` that returns the reversed
   string.
2. Restart the worker, call it from the shell with `.delay("hello")`, and read
   the result with `.get()`.
3. Then add a view + URL that fires it and returns the task id, and a status URL
   to read the result. Confirm the response comes back instantly.

Next → [Lesson 04: Calling tasks the right way](04-calling-tasks.md)
