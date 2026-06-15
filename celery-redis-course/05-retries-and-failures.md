# Lesson 05 — Retries, failures & idempotency

This is the lesson that separates toy projects from reliable systems. Background
tasks fail — networks blip, APIs rate-limit, servers restart. Your job is to
make tasks **survive** that.

## Tasks WILL fail. Plan for it.

Three things can go wrong:
1. The task code raises an exception (API down, bad data).
2. The worker is killed mid-task (deploy, crash, OOM).
3. The task runs twice (more common than you'd think — see below).

We handle these with **retries**, **acknowledgement settings**, and
**idempotency**.

## Automatic retries (the easy way)

Tell Celery which exceptions should trigger a retry:

```python
import requests
from celery import shared_task


@shared_task(
    autoretry_for=(requests.RequestException,),  # retry on these errors
    retry_backoff=True,        # wait 1s, 2s, 4s, 8s... between tries
    retry_backoff_max=600,     # but never wait more than 10 min
    retry_jitter=True,         # randomize delays a bit (avoid thundering herd)
    max_retries=5,             # give up after 5 tries
)
def fetch_exchange_rate(currency):
    resp = requests.get(f"https://api.example.com/rate/{currency}", timeout=10)
    resp.raise_for_status()
    return resp.json()["rate"]
```

If the API throws `RequestException`, Celery automatically re-queues the task
with exponential backoff. You write zero retry logic.

## Manual retries (more control)

When you need custom logic, retry by hand using `bind=True` (gives you `self`):

```python
@shared_task(bind=True, max_retries=3)
def charge_card(self, payment_id):
    try:
        gateway.charge(payment_id)
    except TemporaryGatewayError as exc:
        # retry in 60s; raises MaxRetriesExceededError after max_retries
        raise self.retry(exc=exc, countdown=60)
    except CardDeclinedError:
        # permanent failure — do NOT retry, just record it
        Payment.objects.filter(pk=payment_id).update(status="declined")
        return "declined"
```

Key idea: **distinguish temporary failures (retry) from permanent ones (don't).**
Retrying a declined card forever is pointless and harmful.

## Timeouts — don't let a task run forever

A task stuck on a hung network call ties up a worker indefinitely. Set limits:

```python
@shared_task(
    soft_time_limit=30,   # raise SoftTimeLimitExceeded at 30s (you can clean up)
    time_limit=60,        # hard kill the worker process at 60s
)
def generate_report(report_id):
    try:
        do_heavy_work(report_id)
    except SoftTimeLimitExceeded:
        cleanup_partial(report_id)
        raise
```

- `soft_time_limit`: raises an exception you can catch to clean up.
- `time_limit`: SIGKILL — no cleanup, used as a hard backstop.

You can also set global defaults in settings:
```python
CELERY_TASK_SOFT_TIME_LIMIT = 60
CELERY_TASK_TIME_LIMIT = 120
```

## The big one: idempotency

> **Idempotent** = running the task twice has the same effect as running it once.

Why care? Because **tasks can run more than once.** By default Celery
acknowledges a message *when the worker receives it* (`acks_late=False`). But
even with safer settings, a worker crash or a retry can cause re-execution.
Assume **at-least-once** delivery, not exactly-once.

The danger:
```python
# ❌ NOT idempotent — runs twice = customer charged twice 😱
@shared_task
def charge(order_id):
    amount = Order.objects.get(pk=order_id).total
    payment_gateway.charge(amount)
```

Make it idempotent — check whether the work is already done:
```python
# ✅ Idempotent — safe to run twice
@shared_task
def charge(order_id):
    order = Order.objects.get(pk=order_id)
    if order.is_paid:               # already charged? do nothing.
        return "already paid"
    charge_id = payment_gateway.charge(order.total, idempotency_key=str(order_id))
    order.is_paid = True
    order.charge_id = charge_id
    order.save()
```

Techniques for idempotency:
- **Guard with state**: check a flag/status before doing the work.
- **Idempotency keys**: many APIs (Stripe, etc.) accept a key so duplicate
  requests are de-duped server-side. Use a stable key like the order id.
- **`get_or_create` / `update_or_create`** instead of blind `create`.
- **Unique constraints** in the DB as a last line of defense.

## `acks_late` — for tasks that must not be lost

By default, if a worker dies mid-task, that task is **lost** (it was already
acknowledged). For critical tasks, flip this:

```python
@shared_task(acks_late=True)        # ack only AFTER the task completes
def critical_job(...):
    ...
```

With `acks_late=True`, if the worker dies mid-task, the message goes back to the
queue and another worker picks it up. **Trade-off:** the task may now run twice —
which is exactly why **`acks_late` and idempotency go together.** Use both for
anything you can't afford to lose.

## What happens when retries are exhausted?

The task ends in state `FAILURE` and the exception is stored (if you have a
result backend). For important tasks, handle final failure explicitly:

```python
@shared_task(bind=True, max_retries=3)
def sync_to_crm(self, lead_id):
    try:
        crm.push(lead_id)
    except CRMError as exc:
        try:
            raise self.retry(exc=exc, countdown=30)
        except self.MaxRetriesExceededError:
            Lead.objects.filter(pk=lead_id).update(sync_failed=True)
            # alert a human, send to a dead-letter table, etc.
```

## Exercise

1. Write `unreliable_fetch(url)` that calls `requests.get` and randomly raises
   half the time (`if random.random() < 0.5: raise ...`). Add `autoretry_for`
   with `retry_backoff` and watch it retry in the worker logs.
2. Take your `charge`/order task (or invent one) and make it **idempotent** with
   a state guard. Call it twice with the same id — confirm the work happens once.
3. Add a `soft_time_limit` to `slow_square` lower than its sleep and watch it get
   interrupted.

Next → [Lesson 06: Scheduling with Celery Beat](06-scheduling-beat.md)
