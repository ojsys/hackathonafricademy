# Lesson 04 — Calling tasks the right way

You know `.delay()`. Now learn the full toolbox so you can control *when*, *how*,
and *with what* a task runs.

## `delay()` is just a shortcut

These two are identical:

```python
add.delay(4, 6)
add.apply_async(args=[4, 6])
```

`delay()` is the friendly shorthand. `apply_async()` is the powerful version that
takes options. Use `delay()` for the simple case; reach for `apply_async()` when
you need control.

## Passing arguments — keep them small and serializable

Task arguments are **serialized to JSON** and sent through Redis. This has big
consequences:

✅ **Pass simple, JSON-friendly values:** ids, strings, numbers, lists, dicts.

❌ **Do NOT pass:**
- Django model instances (they don't serialize, and the data is stale by the
  time the worker runs).
- File handles, DB connections, request objects, querysets.

**The golden rule:** pass the **id**, re-fetch inside the task.

```python
# ❌ BAD — passing a model object
def view(request):
    order = Order.objects.get(pk=1)
    process_order.delay(order)          # breaks / stale data

# ✅ GOOD — pass the id, load fresh in the task
def view(request):
    process_order.delay(order_id=1)

@shared_task
def process_order(order_id):
    order = Order.objects.get(pk=order_id)   # always current
    ...
```

Why? The task might run seconds or minutes later. Re-fetching guarantees you
work with the *current* row, not a snapshot from when you queued it.

## Scheduling: run later, not now

`apply_async` lets you delay execution:

```python
# Run in 10 minutes
send_reminder.apply_async(args=[user_id], countdown=600)

# Run at a specific datetime
from datetime import datetime, timedelta
run_at = datetime.utcnow() + timedelta(hours=2)
send_reminder.apply_async(args=[user_id], eta=run_at)
```

- `countdown=N` — wait N seconds before running.
- `eta=datetime` — run at (roughly) this absolute time.

> This is *one-off* delaying. For *recurring* schedules ("every night at 2am"),
> use Celery Beat — Lesson 06.

## Useful `apply_async` options

```python
send_email.apply_async(
    kwargs={"user_id": 42},
    countdown=30,             # wait 30s
    queue="emails",          # route to a specific queue (Lesson 09)
    priority=5,               # higher = sooner (broker-dependent)
    expires=300,              # if not started within 300s, discard it
    retry=True,               # retry sending to broker if broker is flaky
)
```

`expires` is great for time-sensitive work: "if we couldn't even start this
within 5 minutes, it's no longer worth doing — drop it."

## Naming and signatures (`.s()`)

A **signature** packages "a task + its args" into an object you can pass around
without running it yet. You'll need this for workflows (Lesson 07):

```python
from celery import signature

sig = add.s(2, 3)      # a frozen "call add(2,3)" — not run yet
sig.delay()            # now run it → 5
```

Think of `.s()` as "prepare the call"; `.delay()`/`apply_async()` as "fire it."

## Reading results & status

```python
res = slow_square.delay(9)

res.id          # task id (store this if you need to check later)
res.status      # 'PENDING' | 'STARTED' | 'SUCCESS' | 'FAILURE' | 'RETRY'
res.ready()     # True once finished (success OR failure)
res.successful()# True only if it succeeded
res.result      # the return value, or the exception if it failed
res.get(timeout=5)  # block up to 5s; raises on timeout/failure
```

Reconstruct a result later from just the id:

```python
from celery.result import AsyncResult
AsyncResult("the-saved-task-id").status
```

> Reading results requires a **result backend** configured (we set Redis `/1`).
> If `ignore_result=True` or no backend, status is always `PENDING`.

## `ignore_result` — turn off result storage per task

If a task doesn't return anything you'll read (e.g. "send email"), skip storing
its result to save Redis memory and time:

```python
@shared_task(ignore_result=True)
def send_welcome_email(user_id):
    ...
```

## Exercise

1. Write `notify(user_id, message)` that just `print`s the message.
2. Call it three ways:
   - immediately with `.delay()`
   - 15 seconds later with `countdown`
   - with an `expires=5` and intentionally keep the worker stopped for 6 seconds
     before starting it — confirm the task is **discarded** (never runs).
3. Refactor any earlier task that took an object to take an **id** instead.

Next → [Lesson 05: Retries, failures & idempotency](05-retries-and-failures.md)
