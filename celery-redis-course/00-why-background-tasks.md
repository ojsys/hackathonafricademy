# Lesson 00 — Why background tasks?

## The problem

A Django view is supposed to be *fast*. The user clicks something, your view
runs, and a response comes back. If the response takes 8 seconds, the user
stares at a spinner and your server thread is tied up the whole time.

Some work is genuinely slow:

- Sending email / SMS (talking to an external server)
- Generating a PDF or report
- Resizing or processing uploaded images/video
- Calling a third-party API (payments, AI models, geocoding)
- Importing a big CSV
- Anything that should happen *later* or *on a schedule*

If you do that work *inside* the request, three bad things happen:

1. **The user waits.** Slow pages, timeouts.
2. **Your web server gets clogged.** Each worker thread is blocked on slow work
   instead of serving new requests.
3. **Failures hurt.** If the email server hiccups, the user's whole action fails
   — even though the important part (saving their order) already succeeded.

## The fix: do it later, somewhere else

Instead of doing slow work in the view, you **hand it off**:

```
User clicks "Place order"
   │
   ▼
View: save the order  ───►  return "Success!" instantly  (fast ✅)
   │
   └──► put "send confirmation email" job into a queue
                                   │
                                   ▼
                        a separate worker process
                        picks it up and runs it later
```

The view returns immediately. A background worker handles the email seconds
later. If the email fails, you can retry it without bothering the user.

## Where Celery and Redis come in

- **Celery** is the system that runs those background jobs (the "worker").
- **Redis** is the **message broker** — the queue where jobs wait to be picked
  up. (You can also use RabbitMQ; we use Redis because it's simple and you
  probably already know it as a cache.)

That's the whole idea. Everything else in this course is detail.

## When NOT to use Celery

Be honest — Celery adds moving parts (another server, another process to run).
Don't reach for it when:

- The work is **fast** (a few milliseconds). Just do it in the view.
- You need the result **right now** to render the page. Background tasks are
  fire-and-(mostly)-forget; the user already got their response.
- You only need a *tiny* deferral and can't run extra infrastructure — a
  one-off `threading` call or Django's `send_mail` with a timeout may be enough.

Rule of thumb: **slow, external, retryable, or scheduled → background task.**

## Exercise

Write down 3 things in an app you've built (or want to build) that should be
background tasks, and for each one note *why* (slow? external? scheduled?).
Keep the list — we'll turn one of them into a real task later.

Next → [Lesson 01: The pieces](01-the-pieces.md)
