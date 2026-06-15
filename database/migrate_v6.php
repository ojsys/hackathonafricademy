<?php
/**
 * Migration v6 — Seed the "Celery & Redis for Django Developers" course.
 * Run ONCE via browser, then DELETE this file.
 *
 * Inserts 1 course, 4 modules, and 11 lessons (HTML content) into the LMS.
 * IDEMPOTENT: if the course already exists it is deleted (cascade) and re-seeded,
 * so re-running always yields one clean copy. Touches no other course's data.
 *
 * Access: /database/migrate_v6.php?key=hackathon2026celery
 */

define('MIGRATION_PASSWORD', 'hackathon2026celery');
if (($_GET['key'] ?? '') !== MIGRATION_PASSWORD) {
    http_response_code(403);
    die('<h2>Access denied.</h2><p>Add ?key=hackathon2026celery to the URL.</p>');
}

require_once __DIR__ . '/../config/database.php';
$pdo = db();

header('Content-Type: text/html; charset=utf-8');

$COURSE_TITLE = 'Celery & Redis for Django Developers';
$COURSE_DESC = 'A practical, hands-on mini course that takes Python/Django developers from "what is a background task?" to running reliable, scheduled, monitored async work with Celery and Redis — covering setup, tasks, retries, idempotency, scheduling with Beat, workflows, monitoring, and production deployment.';
$MODULES = [
    [
        'title' => 'Fundamentals',
        'lessons' => [
            [
                'title' => 'Why background tasks?',
                'content' => <<<'EOT_LESSON'
<h2>The problem</h2>
<p>A Django view is supposed to be <em>fast</em>. The user clicks something, your view
runs, and a response comes back. If the response takes 8 seconds, the user
stares at a spinner and your server thread is tied up the whole time.</p>
<p>Some work is genuinely slow:</p>
<ul>
<li>Sending email / SMS (talking to an external server)</li>
<li>Generating a PDF or report</li>
<li>Resizing or processing uploaded images/video</li>
<li>Calling a third-party API (payments, AI models, geocoding)</li>
<li>Importing a big CSV</li>
<li>Anything that should happen <em>later</em> or <em>on a schedule</em></li>
</ul>
<p>If you do that work <em>inside</em> the request, three bad things happen:</p>
<ol>
<li><strong>The user waits.</strong> Slow pages, timeouts.</li>
<li><strong>Your web server gets clogged.</strong> Each worker thread is blocked on slow work
   instead of serving new requests.</li>
<li><strong>Failures hurt.</strong> If the email server hiccups, the user's whole action fails
   — even though the important part (saving their order) already succeeded.</li>
</ol>
<h2>The fix: do it later, somewhere else</h2>
<p>Instead of doing slow work in the view, you <strong>hand it off</strong>:</p>
<pre><code>User clicks &quot;Place order&quot;
   │
   ▼
View: save the order  ───►  return &quot;Success!&quot; instantly  (fast ✅)
   │
   └──► put &quot;send confirmation email&quot; job into a queue
                                   │
                                   ▼
                        a separate worker process
                        picks it up and runs it later
</code></pre>
<p>The view returns immediately. A background worker handles the email seconds
later. If the email fails, you can retry it without bothering the user.</p>
<h2>Where Celery and Redis come in</h2>
<ul>
<li><strong>Celery</strong> is the system that runs those background jobs (the "worker").</li>
<li><strong>Redis</strong> is the <strong>message broker</strong> — the queue where jobs wait to be picked
  up. (You can also use RabbitMQ; we use Redis because it's simple and you
  probably already know it as a cache.)</li>
</ul>
<p>That's the whole idea. Everything else in this course is detail.</p>
<h2>When NOT to use Celery</h2>
<p>Be honest — Celery adds moving parts (another server, another process to run).
Don't reach for it when:</p>
<ul>
<li>The work is <strong>fast</strong> (a few milliseconds). Just do it in the view.</li>
<li>You need the result <strong>right now</strong> to render the page. Background tasks are
  fire-and-(mostly)-forget; the user already got their response.</li>
<li>You only need a <em>tiny</em> deferral and can't run extra infrastructure — a
  one-off <code>threading</code> call or Django's <code>send_mail</code> with a timeout may be enough.</li>
</ul>
<p>Rule of thumb: <strong>slow, external, retryable, or scheduled → background task.</strong></p>
<h2>Exercise</h2>
<p>Write down 3 things in an app you've built (or want to build) that should be
background tasks, and for each one note <em>why</em> (slow? external? scheduled?).
Keep the list — we'll turn one of them into a real task later.</p>
EOT_LESSON
                ,
            ],
            [
                'title' => 'The pieces: broker, worker, result backend',
                'content' => <<<'EOT_LESSON'
<p>Before we touch code, get this picture in your head. It explains 90% of the
confusion beginners have.</p>
<h2>The four players</h2>
<pre><code> ┌─────────────┐      task message       ┌──────────────┐
 │  Your app    │ ───────────────────────►│    Broker     │
 │ (Django web) │   &quot;run send_email(42)&quot;  │   (Redis)     │
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
</code></pre>
<h3>1. Producer — your Django code</h3>
<p>When you call <code>send_email.delay(42)</code>, Django doesn't run the function. It
serializes a little message ("call <code>send_email</code> with arg <code>42</code>") and drops it
into the broker. Then it moves on instantly.</p>
<h3>2. Broker — Redis</h3>
<p>The broker is a <strong>queue</strong>. It holds task messages until a worker is free to run
them. Redis is fast, simple, and great for this. (RabbitMQ is the other common
choice; more robust routing, more setup.)</p>
<blockquote>
<p>The broker is <strong>required</strong>. No broker, no Celery.</p>
</blockquote>
<h3>3. Worker — the Celery process</h3>
<p>A separate program you run (<code>celery -A proj worker</code>). It connects to the broker,
pulls messages off the queue, and actually executes your task functions. You can
run many workers, on many machines, to scale out.</p>
<blockquote>
<p>The worker is a <strong>separate process from your web server.</strong> This trips people
up: starting <code>runserver</code> does NOT start Celery. You run them separately.</p>
</blockquote>
<h3>4. Result backend — optional storage for return values</h3>
<p>If a task <code>return</code>s something and you want to read it later (status, result),
Celery stores it in the <strong>result backend</strong>. This can be Redis, a database, etc.</p>
<blockquote>
<p>The result backend is <strong>optional</strong>. Many tasks (send an email, resize an
image) don't need a result at all — they just do work. Don't enable a result
backend unless you actually read results; it costs storage and time.</p>
</blockquote>
<h2>Redis plays two roles (don't confuse them)</h2>
<p>Redis can be <strong>both</strong> the broker <em>and</em> the result backend — but they're
different jobs:</p>
<table>
<thead>
<tr>
<th>Role</th>
<th>Purpose</th>
<th>Required?</th>
</tr>
</thead>
<tbody>
<tr>
<td>Broker</td>
<td>Holds tasks waiting to run</td>
<td>✅ Yes</td>
</tr>
<tr>
<td>Result backend</td>
<td>Holds return values of finished tasks</td>
<td>⚠️ Only if you read results</td>
</tr>
</tbody>
</table>
<p>We'll often use Redis for both in dev because it's one less thing to install.</p>
<h2>The lifecycle of one task</h2>
<ol>
<li>View calls <code>process_payment.delay(order_id=42)</code>.</li>
<li>Celery serializes <code>{task: process_payment, args: [], kwargs: {order_id: 42}}</code>
   and pushes it to Redis.</li>
<li>View returns a response. <strong>User is done waiting.</strong></li>
<li>A worker, whenever it's free, pops the message off Redis.</li>
<li>Worker runs <code>process_payment(order_id=42)</code>.</li>
<li>If a result backend is configured, the return value (or the error) is stored.</li>
<li>Optionally, your app later checks the result by task id.</li>
</ol>
<h2>Key vocabulary</h2>
<ul>
<li><strong>Task</strong>: a Python function you've registered with Celery (<code>@shared_task</code>).</li>
<li><strong>Message</strong>: the serialized "please run this task" instruction in the queue.</li>
<li><strong>Queue</strong>: a named line of messages in the broker (default queue is <code>celery</code>).</li>
<li><strong>Worker</strong>: the process that runs tasks.</li>
<li><strong><code>delay()</code> / <code>apply_async()</code></strong>: how you <em>send</em> a task to the queue.</li>
<li><strong>AsyncResult</strong>: a handle to a task you sent, used to check its status/result.</li>
</ul>
<h2>Exercise</h2>
<p>In your own words (out loud or written), explain to an imaginary teammate:
1. What happens when you call <code>.delay()</code>?
2. Why is the worker a separate process?
3. When do you need a result backend, and when don't you?</p>
<p>If you can answer those, you're ready to set it up.</p>
EOT_LESSON
                ,
            ],
        ],
    ],
    [
        'title' => 'Building Tasks',
        'lessons' => [
            [
                'title' => 'Install & wire it into Django',
                'content' => <<<'EOT_LESSON'
<p>Time to build the project we'll use for the rest of the course. Type it out.</p>
<h2>Step 1 — Install Redis</h2>
<p>Redis runs as a server. Install and start it:</p>
<p><strong>macOS (Homebrew)</strong></p>
<pre><code class="language-bash">brew install redis
brew services start redis      # runs in background
# or run in foreground: redis-server
</code></pre>
<p><strong>Ubuntu/Debian</strong></p>
<pre><code class="language-bash">sudo apt update &amp;&amp; sudo apt install redis-server
sudo systemctl enable --now redis-server
</code></pre>
<p><strong>Docker (any OS) — the easiest, no install</strong></p>
<pre><code class="language-bash">docker run -d --name redis -p 6379:6379 redis:7
</code></pre>
<p><strong>Verify it's alive:</strong></p>
<pre><code class="language-bash">redis-cli ping
# → PONG
</code></pre>
<p>If you see <code>PONG</code>, your broker is ready.</p>
<h2>Step 2 — Create the Django project</h2>
<pre><code class="language-bash">mkdir celery_demo &amp;&amp; cd celery_demo
python -m venv .venv
source .venv/bin/activate            # Windows: .venv\Scripts\activate

pip install &quot;django&gt;=4.2&quot; &quot;celery&gt;=5.3&quot; &quot;redis&gt;=4.5&quot;

django-admin startproject config .   # note the trailing dot
python manage.py startapp tasks_app
</code></pre>
<p>Add the app in <code>config/settings.py</code>:</p>
<pre><code class="language-python">INSTALLED_APPS = [
    # ...
    &quot;tasks_app&quot;,
]
</code></pre>
<p>Your layout:</p>
<pre><code>celery_demo/
├── config/
│   ├── __init__.py
│   ├── settings.py
│   ├── celery.py        ← we create this next
│   └── ...
├── tasks_app/
│   ├── tasks.py         ← we create this in Lesson 03
│   └── ...
└── manage.py
</code></pre>
<h2>Step 3 — Create the Celery app (<code>config/celery.py</code>)</h2>
<p>This is the standard Django + Celery bootstrap. Create <code>config/celery.py</code>:</p>
<pre><code class="language-python">import os
from celery import Celery

# Tell Celery where Django's settings live (same as manage.py uses)
os.environ.setdefault(&quot;DJANGO_SETTINGS_MODULE&quot;, &quot;config.settings&quot;)

# Create the Celery application instance
app = Celery(&quot;config&quot;)

# Read all CELERY_* settings from Django settings.py, using the &quot;CELERY&quot; namespace
app.config_from_object(&quot;django.conf:settings&quot;, namespace=&quot;CELERY&quot;)

# Auto-discover tasks.py in every installed app
app.autodiscover_tasks()


@app.task(bind=True, ignore_result=True)
def debug_task(self):
    print(f&quot;Request: {self.request!r}&quot;)
</code></pre>
<h2>Step 4 — Load Celery when Django starts (<code>config/__init__.py</code>)</h2>
<p>So that <code>shared_task</code> works everywhere, edit <code>config/__init__.py</code>:</p>
<pre><code class="language-python">from .celery import app as celery_app

__all__ = (&quot;celery_app&quot;,)
</code></pre>
<h2>Step 5 — Configure the broker &amp; backend (<code>config/settings.py</code>)</h2>
<p>Add to the bottom of <code>settings.py</code>:</p>
<pre><code class="language-python"># --- Celery configuration ---
# Broker: where tasks wait. Required.
CELERY_BROKER_URL = &quot;redis://localhost:6379/0&quot;

# Result backend: where return values are stored. Optional — we enable it
# now so we can SEE results while learning. Disable in prod if unused.
CELERY_RESULT_BACKEND = &quot;redis://localhost:6379/1&quot;

# Only accept JSON — safe, readable, the modern default.
CELERY_ACCEPT_CONTENT = [&quot;json&quot;]
CELERY_TASK_SERIALIZER = &quot;json&quot;
CELERY_RESULT_SERIALIZER = &quot;json&quot;

# Use the timezone Django uses.
CELERY_TIMEZONE = &quot;UTC&quot;

# Helpful in development: see tracebacks and behave more predictably.
CELERY_TASK_TRACK_STARTED = True
</code></pre>
<blockquote>
<p>Note the two Redis <strong>databases</strong>: <code>/0</code> for the broker, <code>/1</code> for results.
Redis has 16 numbered DBs (0–15); keeping them separate avoids key clashes.</p>
</blockquote>
<h2>Step 6 — Start the worker</h2>
<p>Open a <strong>second terminal</strong> (keep <code>runserver</code> for later in a third), activate the
venv, and run:</p>
<pre><code class="language-bash">celery -A config worker --loglevel=info
</code></pre>
<p>You should see a startup banner ending with something like:</p>
<pre><code>[config]
- ** ---------- .&gt; transport:   redis://localhost:6379/0
- ** ---------- .&gt; results:     redis://localhost:6379/1
[tasks]
  . config.celery.debug_task
celery@yourhost ready.
</code></pre>
<p>🎉 <code>ready.</code> means the worker connected to Redis and is waiting for tasks.</p>
<blockquote>
<p><strong>On Windows:</strong> the default worker pool can misbehave. If tasks hang, run:
<code>celery -A config worker --loglevel=info --pool=solo</code></p>
</blockquote>
<h2>Common setup errors (and fixes)</h2>
<table>
<thead>
<tr>
<th>Error</th>
<th>Cause</th>
<th>Fix</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>Connection refused</code> to 6379</td>
<td>Redis isn't running</td>
<td>Start Redis; <code>redis-cli ping</code></td>
</tr>
<tr>
<td><code>No module named 'config'</code></td>
<td>Wrong <code>-A</code> name</td>
<td>Use your project package name</td>
</tr>
<tr>
<td>Worker starts but sees no tasks</td>
<td><code>autodiscover</code> / app not in INSTALLED_APPS</td>
<td>Add app, put tasks in <code>tasks.py</code></td>
</tr>
<tr>
<td>Tasks queue but never run</td>
<td>Worker not started, or wrong broker URL</td>
<td>Start worker; check both URLs match</td>
</tr>
</tbody>
</table>
<h2>Exercise</h2>
<p>Get the worker to print <code>ready.</code>. Then, in yet another terminal, run the Django
shell and trigger the debug task:</p>
<pre><code class="language-bash">python manage.py shell
</code></pre>
<pre><code class="language-python">from config.celery import debug_task
debug_task.delay()
</code></pre>
<p>Watch your worker terminal — you should see it receive and run <code>debug_task</code>.
If you see that, your whole pipeline works. If not, use the table above.</p>
EOT_LESSON
                ,
            ],
            [
                'title' => 'Your first task',
                'content' => <<<'EOT_LESSON'
<p>Now we write a <em>real</em> task in an app and call it. We'll start simple, then make
it useful.</p>
<h2><code>@shared_task</code> vs <code>@app.task</code></h2>
<p>You'll see two ways to define tasks:</p>
<ul>
<li><code>@app.task</code> — uses a specific Celery app instance. Fine in <code>celery.py</code>.</li>
<li><code>@shared_task</code> — <strong>the one you should use inside Django apps.</strong> It doesn't
  depend on importing the Celery app, so it's reusable and avoids circular
  imports. Use this in your apps' <code>tasks.py</code>.</li>
</ul>
<h2>Step 1 — Write a task</h2>
<p>Create <code>tasks_app/tasks.py</code>:</p>
<pre><code class="language-python">import time
from celery import shared_task


@shared_task
def add(x, y):
    &quot;&quot;&quot;A trivial task so we can see the round-trip.&quot;&quot;&quot;
    return x + y


@shared_task
def slow_square(n):
    &quot;&quot;&quot;Pretend this is heavy work (e.g. image processing).&quot;&quot;&quot;
    time.sleep(3)          # simulate slow work
    return n * n
</code></pre>
<p>Restart your worker so it picks up the new tasks (workers don't hot-reload code
by default). Stop it with <code>Ctrl+C</code> and start it again:</p>
<pre><code class="language-bash">celery -A config worker --loglevel=info
</code></pre>
<p>In the <code>[tasks]</code> section of the banner you should now see:</p>
<pre><code>[tasks]
  . tasks_app.tasks.add
  . tasks_app.tasks.slow_square
</code></pre>
<blockquote>
<p><strong>Remember:</strong> every time you change task code, <strong>restart the worker.</strong> This is
the #1 "why isn't my change working?!" gotcha. (See <code>--autoreload</code> /
<code>watchdog</code> tricks in Lesson 08 for dev convenience.)</p>
</blockquote>
<h2>Step 2 — Call it from the shell</h2>
<pre><code class="language-bash">python manage.py shell
</code></pre>
<pre><code class="language-python">from tasks_app.tasks import add

# Call it asynchronously — this returns IMMEDIATELY with a handle.
result = add.delay(4, 6)

result            # &lt;AsyncResult: 3f7c...-...&gt;
result.id         # the task id (a uuid string)
result.ready()    # False until the worker finishes
result.get()      # 10  (blocks until done — see warning below)
</code></pre>
<p>Watch the worker terminal: you'll see it receive <code>add</code>, run it, and report
<code>succeeded ... result: 10</code>.</p>
<h3>⚠️ <code>result.get()</code> blocks — be careful</h3>
<p><code>.get()</code> waits for the task to finish. That's fine in the shell to learn, but
<strong>never call <code>.get()</code> inside a Django view</strong> — you'd be back to making the user
wait, defeating the entire point. (It can also deadlock if a task calls <code>.get()</code>
on another task.) In real code you usually fire the task and move on, or poll
<code>result.ready()</code> / check status later.</p>
<h2>Step 3 — Call a task from a view</h2>
<p>Let's do the realistic version. In <code>tasks_app/views.py</code>:</p>
<pre><code class="language-python">from django.http import JsonResponse
from .tasks import slow_square


def kick_off(request, n):
    # Fire the task — returns instantly, does NOT wait for the result.
    task = slow_square.delay(n)
    return JsonResponse({&quot;task_id&quot;: task.id, &quot;status&quot;: &quot;queued&quot;})


def check_status(request, task_id):
    from celery.result import AsyncResult
    res = AsyncResult(task_id)
    return JsonResponse({
        &quot;task_id&quot;: task_id,
        &quot;status&quot;: res.status,           # PENDING / STARTED / SUCCESS / FAILURE
        &quot;result&quot;: res.result if res.ready() else None,
    })
</code></pre>
<p>Wire up <code>config/urls.py</code>:</p>
<pre><code class="language-python">from django.urls import path
from tasks_app import views

urlpatterns = [
    path(&quot;run/&lt;int:n&gt;/&quot;, views.kick_off),
    path(&quot;status/&lt;str:task_id&gt;/&quot;, views.check_status),
]
</code></pre>
<p>Now run all three processes:</p>
<pre><code class="language-bash"># terminal 1: Redis (already running)
# terminal 2:
celery -A config worker --loglevel=info
# terminal 3:
python manage.py runserver
</code></pre>
<p>Try it:
1. Visit <code>http://localhost:8000/run/5/</code> → instantly returns a <code>task_id</code>. ✅
   (No 3-second wait, even though the task sleeps 3 seconds.)
2. Copy the id, visit <code>http://localhost:8000/status/&lt;task_id&gt;/</code>.
   - Right away: <code>"status": "STARTED"</code> (or <code>PENDING</code>), <code>"result": null</code>.
   - After ~3 seconds, refresh: <code>"status": "SUCCESS"</code>, <code>"result": 25</code>. 🎉</p>
<p>You just built the fundamental async pattern: <strong>kick off → return immediately →
poll for status/result.</strong></p>
<h2>What "PENDING" really means (gotcha)</h2>
<p><code>PENDING</code> doesn't only mean "queued and waiting." Celery returns <code>PENDING</code> for
<strong>any task id it doesn't know about</strong> — including typos and ids whose results
expired. It's the default "I have no information" state. Don't treat <code>PENDING</code>
as a guarantee the task exists.</p>
<h2>Exercise</h2>
<ol>
<li>Add a <code>@shared_task</code> called <code>reverse_string(text)</code> that returns the reversed
   string.</li>
<li>Restart the worker, call it from the shell with <code>.delay("hello")</code>, and read
   the result with <code>.get()</code>.</li>
<li>Then add a view + URL that fires it and returns the task id, and a status URL
   to read the result. Confirm the response comes back instantly.</li>
</ol>
EOT_LESSON
                ,
            ],
            [
                'title' => 'Calling tasks the right way',
                'content' => <<<'EOT_LESSON'
<p>You know <code>.delay()</code>. Now learn the full toolbox so you can control <em>when</em>, <em>how</em>,
and <em>with what</em> a task runs.</p>
<h2><code>delay()</code> is just a shortcut</h2>
<p>These two are identical:</p>
<pre><code class="language-python">add.delay(4, 6)
add.apply_async(args=[4, 6])
</code></pre>
<p><code>delay()</code> is the friendly shorthand. <code>apply_async()</code> is the powerful version that
takes options. Use <code>delay()</code> for the simple case; reach for <code>apply_async()</code> when
you need control.</p>
<h2>Passing arguments — keep them small and serializable</h2>
<p>Task arguments are <strong>serialized to JSON</strong> and sent through Redis. This has big
consequences:</p>
<p>✅ <strong>Pass simple, JSON-friendly values:</strong> ids, strings, numbers, lists, dicts.</p>
<p>❌ <strong>Do NOT pass:</strong>
- Django model instances (they don't serialize, and the data is stale by the
  time the worker runs).
- File handles, DB connections, request objects, querysets.</p>
<p><strong>The golden rule:</strong> pass the <strong>id</strong>, re-fetch inside the task.</p>
<pre><code class="language-python"># ❌ BAD — passing a model object
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
</code></pre>
<p>Why? The task might run seconds or minutes later. Re-fetching guarantees you
work with the <em>current</em> row, not a snapshot from when you queued it.</p>
<h2>Scheduling: run later, not now</h2>
<p><code>apply_async</code> lets you delay execution:</p>
<pre><code class="language-python"># Run in 10 minutes
send_reminder.apply_async(args=[user_id], countdown=600)

# Run at a specific datetime
from datetime import datetime, timedelta
run_at = datetime.utcnow() + timedelta(hours=2)
send_reminder.apply_async(args=[user_id], eta=run_at)
</code></pre>
<ul>
<li><code>countdown=N</code> — wait N seconds before running.</li>
<li><code>eta=datetime</code> — run at (roughly) this absolute time.</li>
</ul>
<blockquote>
<p>This is <em>one-off</em> delaying. For <em>recurring</em> schedules ("every night at 2am"),
use Celery Beat — Lesson 06.</p>
</blockquote>
<h2>Useful <code>apply_async</code> options</h2>
<pre><code class="language-python">send_email.apply_async(
    kwargs={&quot;user_id&quot;: 42},
    countdown=30,             # wait 30s
    queue=&quot;emails&quot;,          # route to a specific queue (Lesson 09)
    priority=5,               # higher = sooner (broker-dependent)
    expires=300,              # if not started within 300s, discard it
    retry=True,               # retry sending to broker if broker is flaky
)
</code></pre>
<p><code>expires</code> is great for time-sensitive work: "if we couldn't even start this
within 5 minutes, it's no longer worth doing — drop it."</p>
<h2>Naming and signatures (<code>.s()</code>)</h2>
<p>A <strong>signature</strong> packages "a task + its args" into an object you can pass around
without running it yet. You'll need this for workflows (Lesson 07):</p>
<pre><code class="language-python">from celery import signature

sig = add.s(2, 3)      # a frozen &quot;call add(2,3)&quot; — not run yet
sig.delay()            # now run it → 5
</code></pre>
<p>Think of <code>.s()</code> as "prepare the call"; <code>.delay()</code>/<code>apply_async()</code> as "fire it."</p>
<h2>Reading results &amp; status</h2>
<pre><code class="language-python">res = slow_square.delay(9)

res.id          # task id (store this if you need to check later)
res.status      # 'PENDING' | 'STARTED' | 'SUCCESS' | 'FAILURE' | 'RETRY'
res.ready()     # True once finished (success OR failure)
res.successful()# True only if it succeeded
res.result      # the return value, or the exception if it failed
res.get(timeout=5)  # block up to 5s; raises on timeout/failure
</code></pre>
<p>Reconstruct a result later from just the id:</p>
<pre><code class="language-python">from celery.result import AsyncResult
AsyncResult(&quot;the-saved-task-id&quot;).status
</code></pre>
<blockquote>
<p>Reading results requires a <strong>result backend</strong> configured (we set Redis <code>/1</code>).
If <code>ignore_result=True</code> or no backend, status is always <code>PENDING</code>.</p>
</blockquote>
<h2><code>ignore_result</code> — turn off result storage per task</h2>
<p>If a task doesn't return anything you'll read (e.g. "send email"), skip storing
its result to save Redis memory and time:</p>
<pre><code class="language-python">@shared_task(ignore_result=True)
def send_welcome_email(user_id):
    ...
</code></pre>
<h2>Exercise</h2>
<ol>
<li>Write <code>notify(user_id, message)</code> that just <code>print</code>s the message.</li>
<li>Call it three ways:
   - immediately with <code>.delay()</code>
   - 15 seconds later with <code>countdown</code>
   - with an <code>expires=5</code> and intentionally keep the worker stopped for 6 seconds
     before starting it — confirm the task is <strong>discarded</strong> (never runs).</li>
<li>Refactor any earlier task that took an object to take an <strong>id</strong> instead.</li>
</ol>
EOT_LESSON
                ,
            ],
        ],
    ],
    [
        'title' => 'Reliability & Scheduling',
        'lessons' => [
            [
                'title' => 'Retries, failures & idempotency',
                'content' => <<<'EOT_LESSON'
<p>This is the lesson that separates toy projects from reliable systems. Background
tasks fail — networks blip, APIs rate-limit, servers restart. Your job is to
make tasks <strong>survive</strong> that.</p>
<h2>Tasks WILL fail. Plan for it.</h2>
<p>Three things can go wrong:
1. The task code raises an exception (API down, bad data).
2. The worker is killed mid-task (deploy, crash, OOM).
3. The task runs twice (more common than you'd think — see below).</p>
<p>We handle these with <strong>retries</strong>, <strong>acknowledgement settings</strong>, and
<strong>idempotency</strong>.</p>
<h2>Automatic retries (the easy way)</h2>
<p>Tell Celery which exceptions should trigger a retry:</p>
<pre><code class="language-python">import requests
from celery import shared_task


@shared_task(
    autoretry_for=(requests.RequestException,),  # retry on these errors
    retry_backoff=True,        # wait 1s, 2s, 4s, 8s... between tries
    retry_backoff_max=600,     # but never wait more than 10 min
    retry_jitter=True,         # randomize delays a bit (avoid thundering herd)
    max_retries=5,             # give up after 5 tries
)
def fetch_exchange_rate(currency):
    resp = requests.get(f&quot;https://api.example.com/rate/{currency}&quot;, timeout=10)
    resp.raise_for_status()
    return resp.json()[&quot;rate&quot;]
</code></pre>
<p>If the API throws <code>RequestException</code>, Celery automatically re-queues the task
with exponential backoff. You write zero retry logic.</p>
<h2>Manual retries (more control)</h2>
<p>When you need custom logic, retry by hand using <code>bind=True</code> (gives you <code>self</code>):</p>
<pre><code class="language-python">@shared_task(bind=True, max_retries=3)
def charge_card(self, payment_id):
    try:
        gateway.charge(payment_id)
    except TemporaryGatewayError as exc:
        # retry in 60s; raises MaxRetriesExceededError after max_retries
        raise self.retry(exc=exc, countdown=60)
    except CardDeclinedError:
        # permanent failure — do NOT retry, just record it
        Payment.objects.filter(pk=payment_id).update(status=&quot;declined&quot;)
        return &quot;declined&quot;
</code></pre>
<p>Key idea: <strong>distinguish temporary failures (retry) from permanent ones (don't).</strong>
Retrying a declined card forever is pointless and harmful.</p>
<h2>Timeouts — don't let a task run forever</h2>
<p>A task stuck on a hung network call ties up a worker indefinitely. Set limits:</p>
<pre><code class="language-python">@shared_task(
    soft_time_limit=30,   # raise SoftTimeLimitExceeded at 30s (you can clean up)
    time_limit=60,        # hard kill the worker process at 60s
)
def generate_report(report_id):
    try:
        do_heavy_work(report_id)
    except SoftTimeLimitExceeded:
        cleanup_partial(report_id)
        raise
</code></pre>
<ul>
<li><code>soft_time_limit</code>: raises an exception you can catch to clean up.</li>
<li><code>time_limit</code>: SIGKILL — no cleanup, used as a hard backstop.</li>
</ul>
<p>You can also set global defaults in settings:</p>
<pre><code class="language-python">CELERY_TASK_SOFT_TIME_LIMIT = 60
CELERY_TASK_TIME_LIMIT = 120
</code></pre>
<h2>The big one: idempotency</h2>
<blockquote>
<p><strong>Idempotent</strong> = running the task twice has the same effect as running it once.</p>
</blockquote>
<p>Why care? Because <strong>tasks can run more than once.</strong> By default Celery
acknowledges a message <em>when the worker receives it</em> (<code>acks_late=False</code>). But
even with safer settings, a worker crash or a retry can cause re-execution.
Assume <strong>at-least-once</strong> delivery, not exactly-once.</p>
<p>The danger:</p>
<pre><code class="language-python"># ❌ NOT idempotent — runs twice = customer charged twice 😱
@shared_task
def charge(order_id):
    amount = Order.objects.get(pk=order_id).total
    payment_gateway.charge(amount)
</code></pre>
<p>Make it idempotent — check whether the work is already done:</p>
<pre><code class="language-python"># ✅ Idempotent — safe to run twice
@shared_task
def charge(order_id):
    order = Order.objects.get(pk=order_id)
    if order.is_paid:               # already charged? do nothing.
        return &quot;already paid&quot;
    charge_id = payment_gateway.charge(order.total, idempotency_key=str(order_id))
    order.is_paid = True
    order.charge_id = charge_id
    order.save()
</code></pre>
<p>Techniques for idempotency:
- <strong>Guard with state</strong>: check a flag/status before doing the work.
- <strong>Idempotency keys</strong>: many APIs (Stripe, etc.) accept a key so duplicate
  requests are de-duped server-side. Use a stable key like the order id.
- <strong><code>get_or_create</code> / <code>update_or_create</code></strong> instead of blind <code>create</code>.
- <strong>Unique constraints</strong> in the DB as a last line of defense.</p>
<h2><code>acks_late</code> — for tasks that must not be lost</h2>
<p>By default, if a worker dies mid-task, that task is <strong>lost</strong> (it was already
acknowledged). For critical tasks, flip this:</p>
<pre><code class="language-python">@shared_task(acks_late=True)        # ack only AFTER the task completes
def critical_job(...):
    ...
</code></pre>
<p>With <code>acks_late=True</code>, if the worker dies mid-task, the message goes back to the
queue and another worker picks it up. <strong>Trade-off:</strong> the task may now run twice —
which is exactly why <strong><code>acks_late</code> and idempotency go together.</strong> Use both for
anything you can't afford to lose.</p>
<h2>What happens when retries are exhausted?</h2>
<p>The task ends in state <code>FAILURE</code> and the exception is stored (if you have a
result backend). For important tasks, handle final failure explicitly:</p>
<pre><code class="language-python">@shared_task(bind=True, max_retries=3)
def sync_to_crm(self, lead_id):
    try:
        crm.push(lead_id)
    except CRMError as exc:
        try:
            raise self.retry(exc=exc, countdown=30)
        except self.MaxRetriesExceededError:
            Lead.objects.filter(pk=lead_id).update(sync_failed=True)
            # alert a human, send to a dead-letter table, etc.
</code></pre>
<h2>Exercise</h2>
<ol>
<li>Write <code>unreliable_fetch(url)</code> that calls <code>requests.get</code> and randomly raises
   half the time (<code>if random.random() &lt; 0.5: raise ...</code>). Add <code>autoretry_for</code>
   with <code>retry_backoff</code> and watch it retry in the worker logs.</li>
<li>Take your <code>charge</code>/order task (or invent one) and make it <strong>idempotent</strong> with
   a state guard. Call it twice with the same id — confirm the work happens once.</li>
<li>Add a <code>soft_time_limit</code> to <code>slow_square</code> lower than its sleep and watch it get
   interrupted.</li>
</ol>
EOT_LESSON
                ,
            ],
            [
                'title' => 'Scheduling with Celery Beat',
                'content' => <<<'EOT_LESSON'
<p>So far we <em>fire</em> tasks from code. But lots of work should run <strong>automatically on
a schedule</strong>: nightly reports, hourly cleanup, weekly digests. That's <strong>Celery
Beat</strong>.</p>
<h2>What Beat is</h2>
<p>Beat is a separate <strong>scheduler</strong> process. It doesn't run tasks itself — it just
<em>sends</em> them to the queue at the right times, and your normal workers run them.</p>
<pre><code> ┌────────────┐  &quot;it's 2am, queue nightly_report&quot;  ┌─────────┐  ┌─────────┐
 │ Celery Beat │ ──────────────────────────────────►│  Redis  │─►│ Worker  │
 │ (scheduler) │                                     │ (queue) │  │ (runs)  │
 └────────────┘                                     └─────────┘  └─────────┘
</code></pre>
<blockquote>
<p>You run <strong>Beat</strong> <em>and</em> a <strong>worker</strong>. Beat alone queues tasks but nothing runs
them; a worker alone never gets scheduled tasks. You need both.</p>
</blockquote>
<h2>Option A — schedule in code (<code>beat_schedule</code>)</h2>
<p>Good for fixed schedules that live with your code. Add to <code>config/settings.py</code>:</p>
<pre><code class="language-python">from celery.schedules import crontab

CELERY_BEAT_SCHEDULE = {
    # run every 30 seconds (great for testing)
    &quot;heartbeat-every-30s&quot;: {
        &quot;task&quot;: &quot;tasks_app.tasks.heartbeat&quot;,
        &quot;schedule&quot;: 30.0,                 # seconds (float)
    },
    # run every day at 02:00
    &quot;nightly-report&quot;: {
        &quot;task&quot;: &quot;tasks_app.tasks.generate_daily_report&quot;,
        &quot;schedule&quot;: crontab(hour=2, minute=0),
    },
    # run every Monday at 08:30
    &quot;weekly-digest&quot;: {
        &quot;task&quot;: &quot;tasks_app.tasks.send_weekly_digest&quot;,
        &quot;schedule&quot;: crontab(hour=8, minute=30, day_of_week=&quot;mon&quot;),
        &quot;args&quot;: (),                       # positional args for the task
        &quot;kwargs&quot;: {},                     # keyword args
    },
    # run every 15 minutes
    &quot;cleanup&quot;: {
        &quot;task&quot;: &quot;tasks_app.tasks.cleanup_temp_files&quot;,
        &quot;schedule&quot;: crontab(minute=&quot;*/15&quot;),
    },
}
</code></pre>
<p>Add the tasks in <code>tasks_app/tasks.py</code>:</p>
<pre><code class="language-python">@shared_task
def heartbeat():
    print(&quot;💓 beat is alive&quot;)

@shared_task
def generate_daily_report():
    ...

@shared_task
def cleanup_temp_files():
    ...
</code></pre>
<h2><code>crontab()</code> cheat sheet</h2>
<pre><code class="language-python">crontab()                                  # every minute
crontab(minute=0)                          # top of every hour
crontab(minute=&quot;*/15&quot;)                     # every 15 minutes
crontab(hour=2, minute=0)                  # daily at 02:00
crontab(hour=&quot;*/3&quot;)                        # every 3 hours
crontab(day_of_week=&quot;mon&quot;, hour=8, minute=30)   # Mondays 08:30
crontab(day_of_month=1, hour=0, minute=0)       # 1st of month, midnight
</code></pre>
<p>Times follow <code>CELERY_TIMEZONE</code>. Set it correctly (e.g. <code>"Africa/Lagos"</code>) or
your "2am" job runs at the wrong local time.</p>
<h2>Run Beat</h2>
<p>In a <strong>new terminal</strong> (you now have Redis + worker + beat, plus runserver):</p>
<pre><code class="language-bash">celery -A config beat --loglevel=info
</code></pre>
<p>With the 30-second heartbeat above, watch your <strong>worker</strong> terminal — every 30s
it receives and runs <code>heartbeat</code>, printing <code>💓 beat is alive</code>. That confirms the
whole schedule pipeline works.</p>
<blockquote>
<p><strong>Run only ONE beat process.</strong> Two beat processes = every scheduled task fires
twice. Workers you can scale freely; beat must be a singleton.</p>
</blockquote>
<h3>Dev shortcut: combine worker + beat</h3>
<p>For local dev only, you can embed beat in the worker:</p>
<pre><code class="language-bash">celery -A config worker --beat --loglevel=info
</code></pre>
<p>Don't do this in production — keep them separate so you can scale and restart
workers without disturbing the schedule.</p>
<h2>Option B — manage schedules in the Django admin (django-celery-beat)</h2>
<p>Hard-coding schedules means a code deploy to change them. For schedules that
non-developers tweak, use <strong>django-celery-beat</strong>, which stores them in the DB and
exposes them in the Django admin.</p>
<pre><code class="language-bash">pip install django-celery-beat
</code></pre>
<pre><code class="language-python"># settings.py
INSTALLED_APPS += [&quot;django_celery_beat&quot;]
</code></pre>
<pre><code class="language-bash">python manage.py migrate
</code></pre>
<p>Then run beat with the database scheduler:</p>
<pre><code class="language-bash">celery -A config beat -l info --scheduler django_celery_beat.schedulers:DatabaseScheduler
</code></pre>
<p>Now go to the Django admin → <strong>Periodic Tasks</strong>. You can add/edit/disable
schedules through the UI, no deploy needed. You define interval/crontab schedules
and attach tasks to them.</p>
<p><strong>Which to choose?</strong>
- Schedule is fixed and developer-owned → <strong>Option A</strong> (in code). Simpler.
- Schedule changes often or non-devs manage it → <strong>Option B</strong> (admin/DB).</p>
<h2>Gotchas</h2>
<ul>
<li><strong>Beat not running</strong> → scheduled tasks simply never appear. Check the beat
  process is alive.</li>
<li><strong>Two beats</strong> → duplicate runs. Keep it a singleton.</li>
<li><strong>Wrong timezone</strong> → jobs at unexpected hours. Set <code>CELERY_TIMEZONE</code>.</li>
<li><strong>Long task overruns its interval</strong> → e.g. a 20-min task scheduled every 15
  min can pile up. Make such tasks idempotent and/or guard against overlap
  (a Redis lock — see Lesson 09).</li>
<li>Changing <code>CELERY_BEAT_SCHEDULE</code> → <strong>restart beat</strong> to pick it up.</li>
</ul>
<h2>Exercise</h2>
<ol>
<li>Add a <code>heartbeat</code> task on a 20-second schedule. Run beat + worker and confirm
   it prints every 20s.</li>
<li>Add a <code>cleanup_temp_files</code> task scheduled every 5 minutes with <code>crontab</code>.</li>
<li>Install <code>django-celery-beat</code>, migrate, and create the same cleanup schedule
   from the <strong>admin</strong> instead. Disable the code one. Confirm it still fires.</li>
</ol>
EOT_LESSON
                ,
            ],
            [
                'title' => 'Workflows: chains, groups, chords',
                'content' => <<<'EOT_LESSON'
<p>Real features are rarely one task. You often need to run tasks <strong>in sequence</strong>,
<strong>in parallel</strong>, or <strong>fan out then combine</strong>. Celery's <em>Canvas</em> gives you tools
for this. You only need three to start: <strong>chain</strong>, <strong>group</strong>, <strong>chord</strong>.</p>
<p>First, recall signatures from Lesson 04 — <code>.s()</code> packages a call without running
it. Canvas is built entirely from signatures.</p>
<pre><code class="language-python">add.s(2, 3)        # &quot;call add(2, 3)&quot; — a recipe, not a result
</code></pre>
<h2>Chain — run tasks in sequence, piping the result</h2>
<p>A <strong>chain</strong> runs tasks one after another, feeding each result into the next.</p>
<pre><code class="language-python">from celery import chain
from tasks_app.tasks import add, mul

# add(2,3) -&gt; 5, then mul(5,10) -&gt; 50
workflow = chain(add.s(2, 3), mul.s(10))
result = workflow.apply_async()
result.get()        # 50
</code></pre>
<p>Note <code>mul.s(10)</code> has only <strong>one</strong> arg — the previous result (5) is passed in
automatically as the first argument. This "result flows downstream" behavior is
the whole point of a chain.</p>
<p>Shorthand with the <code>|</code> (pipe) operator:</p>
<pre><code class="language-python">(add.s(2, 3) | mul.s(10) | mul.s(2)).apply_async()   # ((2+3)*10)*2 = 100
</code></pre>
<p>Use chains for <strong>dependent steps</strong>: "download file → parse it → save results →
email the user."</p>
<pre><code class="language-python">process = (
    download_file.s(url)
    | parse_csv.s()
    | save_rows.s()
    | notify_user.s(user_id)
)
process.apply_async()
</code></pre>
<h2>Group — run tasks in parallel</h2>
<p>A <strong>group</strong> runs many tasks at the same time (across your workers) and collects
all their results.</p>
<pre><code class="language-python">from celery import group

# square 1..5 in parallel
job = group(slow_square.s(i) for i in range(1, 6))
result = job.apply_async()
result.get()        # [1, 4, 9, 16, 25]
</code></pre>
<p>Use groups for <strong>independent work</strong> you want done concurrently: "resize this
image into 5 sizes," "call 10 APIs at once," "send 1000 emails" (in batches).</p>
<h2>Chord — fan out, then combine (group + callback)</h2>
<p>A <strong>chord</strong> = a group <strong>followed by</strong> a callback that runs once <em>all</em> the group
tasks finish, receiving their results as a list.</p>
<pre><code class="language-python">from celery import chord

# square 1..5 in parallel, THEN sum the results
callback = summarize.s()
result = chord(
    (slow_square.s(i) for i in range(1, 6)),   # the group (header)
    callback,                                   # runs after all finish (body)
).apply_async()
result.get()        # summarize([1,4,9,16,25]) -&gt; 55
</code></pre>
<pre><code class="language-python">@shared_task
def summarize(numbers):
    return sum(numbers)
</code></pre>
<p>Use chords for <strong>map-reduce</strong>: process many items in parallel, then aggregate.
"Generate 50 report sections in parallel → stitch into one PDF → email it."</p>
<blockquote>
<p>Chords need a result backend (the callback waits on the group's results). With
Redis as backend this works out of the box.</p>
</blockquote>
<h2>Combining them</h2>
<p>Canvas pieces nest. A realistic pipeline:</p>
<pre><code class="language-python">from celery import chain, group, chord

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
</code></pre>
<p>Read it top to bottom: fetch → (transform 10 chunks in parallel → merge) → save
→ notify.</p>
<h2>Error handling in workflows</h2>
<p>If a task in a <strong>chain</strong> fails, the rest of the chain stops by default. Attach
an error callback with <code>link_error</code>:</p>
<pre><code class="language-python">chain(
    step_one.s(),
    step_two.s(),
).apply_async(link_error=on_workflow_error.s())

@shared_task
def on_workflow_error(request, exc, traceback):
    logger.error(&quot;Workflow failed: %s&quot;, exc)
</code></pre>
<h2>When NOT to over-engineer</h2>
<p>Canvas is powerful but adds complexity and depends on the result backend. If a
single task can do the job clearly, just write a single task. Reach for Canvas
when you genuinely have <strong>dependent steps</strong> or <strong>parallel fan-out</strong> to express.</p>
<h2>Exercise</h2>
<ol>
<li>Write <code>mul(x, y)</code> and build a chain: <code>add(10, 5) | mul(3)</code> → expect 45.</li>
<li>Build a <code>group</code> that runs <code>slow_square</code> over <code>range(1, 8)</code> in parallel and
   collect the list.</li>
<li>Build a <code>chord</code>: square <code>1..10</code> in parallel, then a <code>summarize</code> callback that
   returns the average. Verify the number.</li>
</ol>
EOT_LESSON
                ,
            ],
        ],
    ],
    [
        'title' => 'Production & Capstone',
        'lessons' => [
            [
                'title' => 'Monitoring & debugging',
                'content' => <<<'EOT_LESSON'
<p>Background tasks are invisible by nature — they run somewhere else, later. So you
need tools to <em>see</em> what's happening. This lesson is about visibility.</p>
<h2>Reading worker logs (your first tool)</h2>
<p>The worker terminal already tells you a lot. Run it with <code>--loglevel=info</code> and
you'll see, per task:</p>
<pre><code>Task tasks_app.tasks.add[3f7c...] received
Task tasks_app.tasks.add[3f7c...] succeeded in 0.002s: 10
</code></pre>
<p>On failure:</p>
<pre><code>Task ...charge[ab12...] raised: CardDeclinedError('insufficient funds')
Traceback (most recent call last): ...
</code></pre>
<p>For deeper detail while debugging, use <code>--loglevel=debug</code>. In production, log to
files / a log aggregator, not just the terminal.</p>
<h2>Flower — the web dashboard</h2>
<p><a href="https://flower.readthedocs.io/">Flower</a> is a real-time web UI for Celery. It's
the single best tool for <em>seeing</em> your task system.</p>
<pre><code class="language-bash">pip install flower
celery -A config flower --port=5555
</code></pre>
<p>Open <code>http://localhost:5555</code>. You get:
- <strong>Workers</strong>: which are online, how many tasks each is processing.
- <strong>Tasks</strong>: live feed of received / started / succeeded / failed, with args,
  runtime, results, and tracebacks.
- <strong>Broker</strong>: queue lengths (how many tasks are waiting).
- Ability to <strong>inspect</strong> and even <strong>revoke</strong> (cancel) tasks.</p>
<p>Spend 10 minutes clicking around Flower while firing tasks — it makes the whole
mental model concrete.</p>
<blockquote>
<p>Protect Flower in production (it exposes task data and controls). Put it behind
auth / a VPN — <code>--basic-auth=user:pass</code> at minimum.</p>
</blockquote>
<h2>Inspecting workers from code</h2>
<p>Celery's <code>inspect</code> API lets you query live worker state:</p>
<pre><code class="language-python">from config.celery import app

i = app.control.inspect()

i.active()       # tasks currently running, per worker
i.scheduled()    # tasks with an eta/countdown waiting to run
i.reserved()     # tasks fetched by a worker but not started yet
i.stats()        # worker stats (pool size, etc.)
i.registered()   # task names each worker knows about
</code></pre>
<p>From the CLI:</p>
<pre><code class="language-bash">celery -A config inspect active
celery -A config inspect ping        # are workers responding?
celery -A config status              # cluster overview
</code></pre>
<h2>Checking the queue directly in Redis</h2>
<p>Since Redis is the broker, you can peek at it:</p>
<pre><code class="language-bash">redis-cli
&gt; LLEN celery          # how many tasks waiting in the default queue
&gt; KEYS *               # see broker/result keys (don't do this on huge prod DBs)
</code></pre>
<p>A growing <code>LLEN</code> means tasks are arriving faster than workers can process them —
time to add workers or speed up tasks.</p>
<h2>Cancelling / revoking tasks</h2>
<pre><code class="language-python">from config.celery import app
app.control.revoke(&quot;task-id-here&quot;)               # don't run it (if not started)
app.control.revoke(&quot;task-id-here&quot;, terminate=True)  # kill it if already running
</code></pre>
<h2>Common bugs and how to diagnose them</h2>
<table>
<thead>
<tr>
<th>Symptom</th>
<th>Likely cause</th>
<th>How to check / fix</th>
</tr>
</thead>
<tbody>
<tr>
<td>Task "queued" but never runs</td>
<td>No worker running, or wrong broker URL</td>
<td><code>celery -A config inspect ping</code>; start worker</td>
</tr>
<tr>
<td>Code change has no effect</td>
<td>Worker still running old code</td>
<td><strong>Restart the worker</strong></td>
</tr>
<tr>
<td>Worker doesn't know the task</td>
<td>Task not in <code>tasks.py</code> / app not installed / not autodiscovered</td>
<td>Check <code>inspect registered</code>; restart worker</td>
</tr>
<tr>
<td><code>result.get()</code> hangs forever</td>
<td>No result backend, or task never ran</td>
<td>Configure backend; verify worker is up</td>
</tr>
<tr>
<td>Status always <code>PENDING</code></td>
<td><code>ignore_result</code>, no backend, or bad task id</td>
<td>Enable backend; verify the id</td>
</tr>
<tr>
<td>Tasks pile up (queue grows)</td>
<td>Too few workers / tasks too slow</td>
<td>Add workers/concurrency; optimize task</td>
</tr>
<tr>
<td>Task runs twice</td>
<td>At-least-once delivery / retries</td>
<td>Make it idempotent (Lesson 05)</td>
</tr>
<tr>
<td><code>Received unregistered task</code></td>
<td>Worker imported different code than producer</td>
<td>Same codebase/version on web + worker; restart</td>
</tr>
</tbody>
</table>
<h2>Auto-reload in development</h2>
<p>Restarting the worker on every change gets old. For dev, use <code>watchdog</code> to
auto-restart on file changes:</p>
<pre><code class="language-bash">pip install watchdog
watchmedo auto-restart --directory=./ --pattern=&quot;*.py&quot; --recursive -- \
    celery -A config worker --loglevel=info
</code></pre>
<p>Now editing a task restarts the worker automatically. (Don't use this in prod.)</p>
<h2>Logging inside tasks</h2>
<p>Use Celery's task logger so messages are tagged with the task name/id:</p>
<pre><code class="language-python">from celery.utils.log import get_task_logger

logger = get_task_logger(__name__)

@shared_task
def process(order_id):
    logger.info(&quot;Processing order %s&quot;, order_id)
    ...
    logger.info(&quot;Done with order %s&quot;, order_id)
</code></pre>
<h2>Errors → alerting (Sentry)</h2>
<p>In production, route task exceptions to Sentry (or similar). The Sentry SDK has
a Celery integration that captures task failures automatically:</p>
<pre><code class="language-python">import sentry_sdk
from sentry_sdk.integrations.celery import CeleryIntegration
sentry_sdk.init(dsn=&quot;...&quot;, integrations=[CeleryIntegration()])
</code></pre>
<h2>Exercise</h2>
<ol>
<li>Install and run Flower. Fire a <code>slow_square</code> and watch it move through
   <code>RECEIVED → STARTED → SUCCESS</code> live.</li>
<li>Make a task fail (raise an exception) and find its traceback in Flower.</li>
<li>From the shell, run <code>app.control.inspect().active()</code> while a <code>slow_square</code>
   (with a longer sleep) is running, and see it listed.</li>
<li>Set up <code>watchmedo</code> auto-restart and confirm editing a task reloads the worker.</li>
</ol>
EOT_LESSON
                ,
            ],
            [
                'title' => 'Production checklist & gotchas',
                'content' => <<<'EOT_LESSON'
<p>You can build tasks now. This lesson is what stands between "works on my laptop"
and "runs reliably in production." Skim it now; return to it before you deploy.</p>
<h2>Running workers in production</h2>
<p>You don't run <code>celery worker</code> in a terminal forever. Use a <strong>process manager</strong>
so it restarts on crash/reboot:</p>
<ul>
<li><strong>systemd</strong> (common on Linux VMs): a <code>celery.service</code> unit + <code>celerybeat.service</code>.</li>
<li><strong>Supervisor</strong>: classic option, simple config.</li>
<li><strong>Docker / Kubernetes</strong>: worker and beat as separate containers/deployments.</li>
</ul>
<p>You run, at minimum, <strong>three</strong> long-lived processes:
1. Web server (gunicorn/uvicorn + Django)
2. Celery <strong>worker(s)</strong>
3. Celery <strong>beat</strong> (only if you use scheduling) — <strong>exactly one</strong> instance</p>
<p>Example systemd worker unit (sketch):</p>
<pre><code class="language-ini">[Unit]
Description=Celery Worker
After=network.target redis.service

[Service]
WorkingDirectory=/srv/app
ExecStart=/srv/app/.venv/bin/celery -A config worker -l info --concurrency=4
Restart=always

[Install]
WantedBy=multi-user.target
</code></pre>
<h2>Concurrency &amp; pools</h2>
<pre><code class="language-bash">celery -A config worker --concurrency=8           # 8 worker processes (prefork)
</code></pre>
<ul>
<li><strong>prefork</strong> (default): multiple processes. Best for <strong>CPU-bound</strong> and general
  use. Set <code>--concurrency</code> near your CPU core count.</li>
<li><strong>gevent/eventlet</strong>: many green threads in one process. Best for <strong>I/O-bound</strong>
  tasks (lots of waiting on network) — e.g. <code>--pool=gevent --concurrency=200</code>.</li>
<li>Rule of thumb: CPU-heavy → prefork; lots of API/network waiting → gevent.</li>
</ul>
<p>Scale out by running <strong>more worker processes/machines</strong>, all pointing at the
same Redis broker.</p>
<h2>Use multiple queues (don't let slow tasks block fast ones)</h2>
<p>By default everything shares one queue. A flood of slow report jobs can starve
your quick email tasks. Split them:</p>
<pre><code class="language-python"># settings.py
CELERY_TASK_ROUTES = {
    &quot;tasks_app.tasks.send_email&quot;:      {&quot;queue&quot;: &quot;fast&quot;},
    &quot;tasks_app.tasks.generate_report&quot;: {&quot;queue&quot;: &quot;slow&quot;},
}
</code></pre>
<p>Run dedicated workers per queue:</p>
<pre><code class="language-bash">celery -A config worker -Q fast -c 8 -n fast@%h
celery -A config worker -Q slow -c 2 -n slow@%h
</code></pre>
<p>Now report jobs can't delay confirmation emails. This is one of the highest-value
production patterns.</p>
<h2>Prefetch tuning</h2>
<p>A worker grabs several tasks at once (<code>prefetch</code>). For <strong>long</strong> tasks this is bad
— one worker hoards tasks while others sit idle. For long-running tasks set:</p>
<pre><code class="language-python">CELERY_WORKER_PREFETCH_MULTIPLIER = 1   # take one at a time
CELERY_TASK_ACKS_LATE = True            # ack after completion (with idempotency!)
</code></pre>
<p>For tons of tiny fast tasks, a higher prefetch is more efficient. Tune to your
workload.</p>
<h2>Preventing overlapping runs (Redis lock)</h2>
<p>For a scheduled task that must never run twice concurrently, use a Redis lock:</p>
<pre><code class="language-python">from django.core.cache import cache   # configured to use Redis

@shared_task
def nightly_sync():
    lock_id = &quot;lock:nightly_sync&quot;
    # acquire lock, auto-expire after 10 min so a crash can't deadlock it
    if not cache.add(lock_id, &quot;1&quot;, timeout=600):
        return &quot;already running, skipping&quot;
    try:
        do_the_work()
    finally:
        cache.delete(lock_id)
</code></pre>
<h2>Result backend hygiene</h2>
<ul>
<li>Don't store results you never read — set <code>task_ignore_result=True</code> globally or
  <code>ignore_result=True</code> per task.</li>
<li>Results expire after <code>CELERY_RESULT_EXPIRES</code> (default 1 day). Keep it sane so
  Redis doesn't fill up:
  <code>python
  CELERY_RESULT_EXPIRES = 3600   # seconds</code></li>
</ul>
<h2>Redis as broker — durability notes</h2>
<ul>
<li>Redis is fast but, in default config, <strong>in-memory</strong>. If Redis restarts and
  isn't persisting, <strong>queued (not-yet-run) tasks can be lost.</strong> Enable Redis
  persistence (AOF) for important queues, or use RabbitMQ for stronger
  guarantees.</li>
<li>Use <strong>separate Redis databases/instances</strong> for broker vs. cache vs. results so
  a <code>FLUSHDB</code> on your cache doesn't nuke your task queue.</li>
<li>Set <code>CELERY_BROKER_TRANSPORT_OPTIONS = {"visibility_timeout": ...}</code> if you have
  long ETA/countdown tasks (longer than the default visibility timeout), or they
  can be redelivered early.</li>
</ul>
<h2>Security &amp; config</h2>
<ul>
<li>Keep <code>CELERY_ACCEPT_CONTENT = ["json"]</code>. <strong>Never</strong> enable the <code>pickle</code>
  serializer on an untrusted broker — it allows arbitrary code execution.</li>
<li>Put secrets (broker URL with password) in env vars, not in code.</li>
<li>Lock down Redis (bind to localhost / private network, require a password,
  don't expose 6379 to the internet).</li>
<li>Protect Flower with auth.</li>
</ul>
<h2>Pre-deploy checklist</h2>
<ul>
<li>[ ] Worker(s) run under systemd/supervisor/k8s with <code>Restart=always</code>.</li>
<li>[ ] Exactly <strong>one</strong> beat instance (if scheduling).</li>
<li>[ ] Broker URL &amp; backend come from env vars/secrets.</li>
<li>[ ] <code>CELERY_ACCEPT_CONTENT = ["json"]</code> (no pickle).</li>
<li>[ ] Tasks pass <strong>ids, not objects</strong>, and re-fetch inside.</li>
<li>[ ] Critical tasks are <strong>idempotent</strong> and use <code>acks_late</code>.</li>
<li>[ ] Time limits set (<code>soft_time_limit</code>/<code>time_limit</code>).</li>
<li>[ ] Retries configured with backoff for external calls.</li>
<li>[ ] Separate queues for fast vs. slow work.</li>
<li>[ ] Result backend expiry set; unused results ignored.</li>
<li>[ ] Monitoring in place (Flower + log aggregation + Sentry).</li>
<li>[ ] Redis persistence considered for important queues.</li>
<li>[ ] Deploy strategy restarts workers so they pick up new code.</li>
</ul>
<h2>Exercise</h2>
<ol>
<li>Add <code>CELERY_TASK_ROUTES</code> to send <code>slow_square</code> to a <code>slow</code> queue and <code>add</code> to
   a <code>fast</code> queue. Start two workers (<code>-Q slow</code>, <code>-Q fast</code>) and confirm via the
   worker logs that each task lands on the right worker.</li>
<li>Add a Redis lock to a task and prove that a second concurrent call returns
   "already running."</li>
<li>Write a systemd (or Supervisor, or Docker Compose) config that runs your
   worker — even if you don't deploy it, get it written.</li>
</ol>
EOT_LESSON
                ,
            ],
            [
                'title' => 'Capstone project',
                'content' => <<<'EOT_LESSON'
<p>Time to put it all together. You'll build a small but realistic feature that
uses almost everything in this course. No new concepts — just assembly.</p>
<h2>The brief: "Bulk Newsletter Sender with reports"</h2>
<p>Build a Django feature where an admin uploads a CSV of subscribers and triggers a
newsletter send. The system must:</p>
<ol>
<li>Accept the upload <strong>without making the admin wait</strong> for the send.</li>
<li>Validate and import subscribers in the background.</li>
<li>Send emails <strong>in parallel</strong>, with <strong>retries</strong> for transient failures.</li>
<li>After all sends finish, generate a <strong>summary report</strong> (sent / failed counts).</li>
<li>Run a <strong>nightly cleanup</strong> of old import files.</li>
<li>Let the admin <strong>check progress</strong> via a status endpoint.</li>
</ol>
<p>This exercises: views firing tasks, id-passing, groups + chord, retries &amp;
idempotency, beat scheduling, and status polling.</p>
<h2>Suggested data model</h2>
<pre><code class="language-python"># models.py
class Campaign(models.Model):
    name = models.CharField(max_length=200)
    created_at = models.DateTimeField(auto_now_add=True)
    status = models.CharField(max_length=20, default=&quot;pending&quot;)  # pending/sending/done
    sent_count = models.IntegerField(default=0)
    failed_count = models.IntegerField(default=0)

class Subscriber(models.Model):
    campaign = models.ForeignKey(Campaign, on_delete=models.CASCADE)
    email = models.EmailField()
    sent = models.BooleanField(default=False)        # for idempotency!
    class Meta:
        unique_together = (&quot;campaign&quot;, &quot;email&quot;)      # de-dupe defense
</code></pre>
<h2>Architecture</h2>
<pre><code>POST /campaigns/  (CSV upload)
   │  save file, create Campaign(status=&quot;pending&quot;), return campaign_id instantly
   ▼
import_subscribers.delay(campaign_id, file_path)        # background import
   │  parses CSV, bulk-creates Subscribers (idempotent)
   ▼
chord(
   group(send_one.s(sub_id) for each subscriber),       # parallel sends + retries
   finalize_campaign.s(campaign_id),                    # runs after all finish
)
   ▼
finalize_campaign  → set status=&quot;done&quot;, tally counts

Beat: cleanup_old_uploads  every night at 03:00
GET /campaigns/&lt;id&gt;/status/ → JSON progress
</code></pre>
<h2>Tasks to implement</h2>
<pre><code class="language-python">from celery import shared_task, chord, group
from celery.utils.log import get_task_logger

logger = get_task_logger(__name__)


@shared_task
def import_subscribers(campaign_id, file_path):
    campaign = Campaign.objects.get(pk=campaign_id)
    campaign.status = &quot;sending&quot;
    campaign.save(update_fields=[&quot;status&quot;])

    emails = read_emails_from_csv(file_path)            # you write this helper
    for email in emails:
        Subscriber.objects.get_or_create(               # idempotent import
            campaign=campaign, email=email
        )

    sub_ids = list(
        Subscriber.objects.filter(campaign=campaign, sent=False)
                          .values_list(&quot;id&quot;, flat=True)
    )
    # fan out the sends, then finalize once all complete
    chord(
        group(send_one.s(sid) for sid in sub_ids),
        finalize_campaign.s(campaign_id),
    ).apply_async()


@shared_task(
    bind=True,
    autoretry_for=(SMTPException,),
    retry_backoff=True,
    max_retries=3,
    acks_late=True,
)
def send_one(self, subscriber_id):
    sub = Subscriber.objects.get(pk=subscriber_id)
    if sub.sent:                                         # idempotency guard
        return &quot;already sent&quot;
    send_actual_email(sub.email)                         # your email function
    sub.sent = True
    sub.save(update_fields=[&quot;sent&quot;])
    return &quot;sent&quot;


@shared_task
def finalize_campaign(results, campaign_id):
    # `results` is the list of return values from every send_one
    campaign = Campaign.objects.get(pk=campaign_id)
    campaign.sent_count = Subscriber.objects.filter(
        campaign=campaign, sent=True).count()
    campaign.failed_count = Subscriber.objects.filter(
        campaign=campaign, sent=False).count()
    campaign.status = &quot;done&quot;
    campaign.save()
    logger.info(&quot;Campaign %s done: %s sent, %s failed&quot;,
                campaign_id, campaign.sent_count, campaign.failed_count)


@shared_task
def cleanup_old_uploads():
    # delete CSV files older than 7 days
    ...
</code></pre>
<h2>Views</h2>
<pre><code class="language-python">def create_campaign(request):
    file_path = save_uploaded_file(request.FILES[&quot;csv&quot;])   # you write this
    campaign = Campaign.objects.create(name=request.POST[&quot;name&quot;])
    import_subscribers.delay(campaign.id, file_path)       # fire &amp; return
    return JsonResponse({&quot;campaign_id&quot;: campaign.id, &quot;status&quot;: &quot;queued&quot;})

def campaign_status(request, campaign_id):
    c = Campaign.objects.get(pk=campaign_id)
    return JsonResponse({
        &quot;status&quot;: c.status,
        &quot;sent&quot;: c.sent_count,
        &quot;failed&quot;: c.failed_count,
    })
</code></pre>
<h2>Beat schedule</h2>
<pre><code class="language-python">CELERY_BEAT_SCHEDULE = {
    &quot;cleanup-old-uploads&quot;: {
        &quot;task&quot;: &quot;tasks_app.tasks.cleanup_old_uploads&quot;,
        &quot;schedule&quot;: crontab(hour=3, minute=0),
    },
}
</code></pre>
<h2>Running the whole thing</h2>
<pre><code class="language-bash"># 1. Redis
redis-cli ping
# 2. Worker
celery -A config worker -l info
# 3. Beat
celery -A config beat -l info
# 4. Flower (watch it work)
celery -A config flower
# 5. Django
python manage.py runserver
</code></pre>
<p>Upload a CSV → get a <code>campaign_id</code> instantly → poll <code>/campaigns/&lt;id&gt;/status/</code> →
watch counts climb in real time while Flower shows the parallel sends.</p>
<h2>Self-check — did you apply each lesson?</h2>
<ul>
<li>[ ] View returns <strong>immediately</strong>; no <code>.get()</code> in the request path. (L00, L03)</li>
<li>[ ] Tasks receive <strong>ids</strong>, re-fetch inside. (L04)</li>
<li>[ ] <code>send_one</code> is <strong>idempotent</strong> (<code>sent</code> guard + <code>get_or_create</code> + unique). (L05)</li>
<li>[ ] Retries with backoff on <code>SMTPException</code>; <code>acks_late=True</code>. (L05)</li>
<li>[ ] <strong>Group</strong> to parallelize sends, <strong>chord</strong> to finalize after. (L07)</li>
<li>[ ] <strong>Beat</strong> schedules nightly cleanup. (L06)</li>
<li>[ ] You watched it in <strong>Flower</strong> and read worker logs. (L08)</li>
<li>[ ] (Bonus) Sends routed to their own <strong>queue</strong>. (L09)</li>
</ul>
<h2>Stretch goals</h2>
<ul>
<li>Add a <code>fast</code>/<code>slow</code> queue split and route sends to a dedicated worker.</li>
<li>Add <code>link_error</code> to record per-send failures into a <code>FailedSend</code> table and
  alert if failure rate &gt; 10%.</li>
<li>Replace polling with WebSockets (Django Channels) to push live progress.</li>
<li>Add a Redis lock so the nightly cleanup can never overlap.</li>
</ul>
<h2>You're done 🎉</h2>
<p>You can now design, build, schedule, secure, and debug background processing in
Django with Celery + Redis. Re-read <a href="09-production-and-gotchas.md">Lesson 09</a>
before any real deploy, and keep Flower open whenever you're developing tasks.</p>
EOT_LESSON
                ,
            ],
        ],
    ],
];
echo '<h2>Seeding course: Celery &amp; Redis for Django Developers</h2>';

$pdo->beginTransaction();
try {
    // --- Idempotency: remove any existing copy of this course (cascade children) ---
    $find = $pdo->prepare('SELECT id FROM courses WHERE title = ?');
    $find->execute([$COURSE_TITLE]);
    foreach ($find->fetchAll(PDO::FETCH_COLUMN) as $oldId) {
        // Manual cascade in case FK pragma is off
        $mods = $pdo->prepare('SELECT id FROM modules WHERE course_id = ?');
        $mods->execute([$oldId]);
        foreach ($mods->fetchAll(PDO::FETCH_COLUMN) as $mid) {
            $pdo->prepare('DELETE FROM lessons WHERE module_id = ?')->execute([$mid]);
        }
        $pdo->prepare('DELETE FROM modules WHERE course_id = ?')->execute([$oldId]);
        $pdo->prepare('DELETE FROM courses WHERE id = ?')->execute([$oldId]);
        echo '<p>&#9851; Removed existing course id ' . (int)$oldId . ' for clean re-seed.</p>';
    }

    // --- Course ---
    $maxOrder = (int)$pdo->query('SELECT COALESCE(MAX(order_index),0) FROM courses')->fetchColumn();
    $ins = $pdo->prepare('INSERT INTO courses (title, description, status, order_index) VALUES (?, ?, \'published\', ?)');
    $ins->execute([$COURSE_TITLE, $COURSE_DESC, $maxOrder + 1]);
    $courseId = (int)$pdo->lastInsertId();
    echo '<p>&#9989; Course created (id ' . $courseId . ').</p>';

    $modOrder = 0;
    $lessonCount = 0;
    foreach ($MODULES as $module) {
        $modOrder++;
        $mStmt = $pdo->prepare('INSERT INTO modules (course_id, title, order_index) VALUES (?, ?, ?)');
        $mStmt->execute([$courseId, $module['title'], $modOrder]);
        $moduleId = (int)$pdo->lastInsertId();
        echo '<p>&#128218; Module ' . $modOrder . ': ' . htmlspecialchars($module['title']) . '</p>';
        $lOrder = 0;
        foreach ($module['lessons'] as $lesson) {
            $lOrder++;
            $lStmt = $pdo->prepare('INSERT INTO lessons (module_id, title, content, order_index) VALUES (?, ?, ?, ?)');
            $lStmt->execute([$moduleId, $lesson['title'], $lesson['content'], $lOrder]);
            $lessonCount++;
            echo '<p style="margin-left:20px">&#8226; ' . htmlspecialchars($lesson['title']) . '</p>';
        }
    }

    $pdo->commit();
    echo '<h3 style="color:green">Done. Seeded 1 course, ' . $modOrder . ' modules, ' . $lessonCount . ' lessons.</h3>';
    echo '<p><strong>Now DELETE this file.</strong></p>';
} catch (Throwable $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo '<h3 style="color:red">Failed: ' . htmlspecialchars($e->getMessage()) . '</h3><p>No changes were saved.</p>';
}
