# Lesson 01 — The pieces: broker, worker, result backend

Before we touch code, get this picture in your head. It explains 90% of the
confusion beginners have.

## The four players

```
 ┌─────────────┐      task message       ┌──────────────┐
 │  Your app    │ ───────────────────────►│    Broker     │
 │ (Django web) │   "run send_email(42)"  │   (Redis)     │
 │  = PRODUCER  │                          │  = the queue  │
 └─────────────┘                          └──────┬───────┘
                                                  │ worker pulls next job
                                                  ▼
                                          ┌──────────────┐
                                          │ Celery worker │
                                          │  = CONSUMER   │
                                          │  runs the code │
                                          └──────┬───────┘
                                                 │ (optional) store result
                                                 ▼
                                          ┌──────────────┐
                                          │ Result backend│
                                          │ (Redis/DB)    │
                                          └──────────────┘
```

### 1. Producer — your Django code
When you call `send_email.delay(42)`, Django doesn't run the function. It
serializes a little message ("call `send_email` with arg `42`") and drops it
into the broker. Then it moves on instantly.

### 2. Broker — Redis
The broker is a **queue**. It holds task messages until a worker is free to run
them. Redis is fast, simple, and great for this. (RabbitMQ is the other common
choice; more robust routing, more setup.)

> The broker is **required**. No broker, no Celery.

### 3. Worker — the Celery process
A separate program you run (`celery -A proj worker`). It connects to the broker,
pulls messages off the queue, and actually executes your task functions. You can
run many workers, on many machines, to scale out.

> The worker is a **separate process from your web server.** This trips people
> up: starting `runserver` does NOT start Celery. You run them separately.

### 4. Result backend — optional storage for return values
If a task `return`s something and you want to read it later (status, result),
Celery stores it in the **result backend**. This can be Redis, a database, etc.

> The result backend is **optional**. Many tasks (send an email, resize an
> image) don't need a result at all — they just do work. Don't enable a result
> backend unless you actually read results; it costs storage and time.

## Redis plays two roles (don't confuse them)

Redis can be **both** the broker *and* the result backend — but they're
different jobs:

| Role | Purpose | Required? |
|------|---------|-----------|
| Broker | Holds tasks waiting to run | ✅ Yes |
| Result backend | Holds return values of finished tasks | ⚠️ Only if you read results |

We'll often use Redis for both in dev because it's one less thing to install.

## The lifecycle of one task

1. View calls `process_payment.delay(order_id=42)`.
2. Celery serializes `{task: process_payment, args: [], kwargs: {order_id: 42}}`
   and pushes it to Redis.
3. View returns a response. **User is done waiting.**
4. A worker, whenever it's free, pops the message off Redis.
5. Worker runs `process_payment(order_id=42)`.
6. If a result backend is configured, the return value (or the error) is stored.
7. Optionally, your app later checks the result by task id.

## Key vocabulary

- **Task**: a Python function you've registered with Celery (`@shared_task`).
- **Message**: the serialized "please run this task" instruction in the queue.
- **Queue**: a named line of messages in the broker (default queue is `celery`).
- **Worker**: the process that runs tasks.
- **`delay()` / `apply_async()`**: how you *send* a task to the queue.
- **AsyncResult**: a handle to a task you sent, used to check its status/result.

## Exercise

In your own words (out loud or written), explain to an imaginary teammate:
1. What happens when you call `.delay()`?
2. Why is the worker a separate process?
3. When do you need a result backend, and when don't you?

If you can answer those, you're ready to set it up.

Next → [Lesson 02: Install & wire it into Django](02-setup-django.md)
