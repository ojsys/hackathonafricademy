# Lesson 07 — Workflows: chains, groups, chords

Real features are rarely one task. You often need to run tasks **in sequence**,
**in parallel**, or **fan out then combine**. Celery's *Canvas* gives you tools
for this. You only need three to start: **chain**, **group**, **chord**.

First, recall signatures from Lesson 04 — `.s()` packages a call without running
it. Canvas is built entirely from signatures.

```python
add.s(2, 3)        # "call add(2, 3)" — a recipe, not a result
```

## Chain — run tasks in sequence, piping the result

A **chain** runs tasks one after another, feeding each result into the next.

```python
from celery import chain
from tasks_app.tasks import add, mul

# add(2,3) -> 5, then mul(5,10) -> 50
workflow = chain(add.s(2, 3), mul.s(10))
result = workflow.apply_async()
result.get()        # 50
```

Note `mul.s(10)` has only **one** arg — the previous result (5) is passed in
automatically as the first argument. This "result flows downstream" behavior is
the whole point of a chain.

Shorthand with the `|` (pipe) operator:
```python
(add.s(2, 3) | mul.s(10) | mul.s(2)).apply_async()   # ((2+3)*10)*2 = 100
```

Use chains for **dependent steps**: "download file → parse it → save results →
email the user."

```python
process = (
    download_file.s(url)
    | parse_csv.s()
    | save_rows.s()
    | notify_user.s(user_id)
)
process.apply_async()
```

## Group — run tasks in parallel

A **group** runs many tasks at the same time (across your workers) and collects
all their results.

```python
from celery import group

# square 1..5 in parallel
job = group(slow_square.s(i) for i in range(1, 6))
result = job.apply_async()
result.get()        # [1, 4, 9, 16, 25]
```

Use groups for **independent work** you want done concurrently: "resize this
image into 5 sizes," "call 10 APIs at once," "send 1000 emails" (in batches).

## Chord — fan out, then combine (group + callback)

A **chord** = a group **followed by** a callback that runs once *all* the group
tasks finish, receiving their results as a list.

```python
from celery import chord

# square 1..5 in parallel, THEN sum the results
callback = summarize.s()
result = chord(
    (slow_square.s(i) for i in range(1, 6)),   # the group (header)
    callback,                                   # runs after all finish (body)
).apply_async()
result.get()        # summarize([1,4,9,16,25]) -> 55
```

```python
@shared_task
def summarize(numbers):
    return sum(numbers)
```

Use chords for **map-reduce**: process many items in parallel, then aggregate.
"Generate 50 report sections in parallel → stitch into one PDF → email it."

> Chords need a result backend (the callback waits on the group's results). With
> Redis as backend this works out of the box.

## Combining them

Canvas pieces nest. A realistic pipeline:

```python
from celery import chain, group, chord

workflow = chain(
    fetch_dataset.s(dataset_id),
    chord(
        group(transform_chunk.s(i) for i in range(10)),  # parallel transform
        merge_chunks.s(),                                  # combine
    ),
    save_result.s(),
    notify_user.s(user_id),
)
workflow.apply_async()
```

Read it top to bottom: fetch → (transform 10 chunks in parallel → merge) → save
→ notify.

## Error handling in workflows

If a task in a **chain** fails, the rest of the chain stops by default. Attach
an error callback with `link_error`:

```python
chain(
    step_one.s(),
    step_two.s(),
).apply_async(link_error=on_workflow_error.s())

@shared_task
def on_workflow_error(request, exc, traceback):
    logger.error("Workflow failed: %s", exc)
```

## When NOT to over-engineer

Canvas is powerful but adds complexity and depends on the result backend. If a
single task can do the job clearly, just write a single task. Reach for Canvas
when you genuinely have **dependent steps** or **parallel fan-out** to express.

## Exercise

1. Write `mul(x, y)` and build a chain: `add(10, 5) | mul(3)` → expect 45.
2. Build a `group` that runs `slow_square` over `range(1, 8)` in parallel and
   collect the list.
3. Build a `chord`: square `1..10` in parallel, then a `summarize` callback that
   returns the average. Verify the number.

Next → [Lesson 08: Monitoring & debugging](08-monitoring-debugging.md)
