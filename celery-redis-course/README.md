# Celery & Redis for Django Developers — A Mini Course

A practical, no-fluff course that takes you from "what even is a background task?"
to running reliable, scheduled, monitored async work in a real Django project.

Each lesson is short, builds on the last, and ends with a small exercise.
Do the exercises — that's where the learning sticks.

## Who this is for

Python/Django developers who can build a normal request/response app but have
never (or barely) used a task queue.

## What you'll be able to do by the end

- Explain *why* and *when* to push work out of the request cycle
- Wire Celery + Redis into a Django project from scratch
- Write, call, and chain tasks safely
- Schedule recurring jobs (Celery Beat)
- Handle retries, failures, and timeouts without losing data
- Monitor what's running and debug what went wrong
- Avoid the classic beginner mistakes that cause data loss and "ghost" tasks

## The mental model in one sentence

> **Celery** is the worker that *runs* your background jobs; **Redis** is the
> mailbox (broker) where jobs wait to be picked up, and optionally where results
> are stored.

## Lessons

| #  | Lesson | What you learn |
|----|--------|----------------|
| 00 | [Why background tasks?](00-why-background-tasks.md) | The problem Celery solves |
| 01 | [The pieces: broker, worker, result backend](01-the-pieces.md) | How Celery + Redis fit together |
| 02 | [Install & wire it into Django](02-setup-django.md) | A working setup from zero |
| 03 | [Your first task](03-first-task.md) | Write & call a task, see it run |
| 04 | [Calling tasks the right way](04-calling-tasks.md) | `delay`, `apply_async`, args, ETA |
| 05 | [Retries, failures & idempotency](05-retries-and-failures.md) | Make tasks survive the real world |
| 06 | [Scheduling with Celery Beat](06-scheduling-beat.md) | Cron-style recurring jobs |
| 07 | [Workflows: chains, groups, chords](07-workflows.md) | Compose tasks together |
| 08 | [Monitoring & debugging](08-monitoring-debugging.md) | Flower, logs, inspecting state |
| 09 | [Production checklist & gotchas](09-production-and-gotchas.md) | Ship it without regrets |
| 10 | [Capstone project](10-capstone-project.md) | Build a real feature end to end |

## How to use this course

1. Read a lesson top to bottom.
2. Type the code yourself (don't copy-paste — typing builds memory).
3. Do the **Exercise** at the bottom before moving on.
4. Keep a single demo project open the whole way through; each lesson adds to it.

## Prerequisites

- Python 3.9+
- Basic Django (you can create a project and an app)
- A terminal you're comfortable in
- Redis installed (we cover this in Lesson 02)

Versions this course targets: **Celery 5.x**, **Redis 6/7**, **Django 4.x/5.x**.

Start here → [Lesson 00: Why background tasks?](00-why-background-tasks.md)
