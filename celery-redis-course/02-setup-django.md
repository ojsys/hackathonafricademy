# Lesson 02 — Install & wire it into Django

Time to build the project we'll use for the rest of the course. Type it out.

## Step 1 — Install Redis

Redis runs as a server. Install and start it:

**macOS (Homebrew)**
```bash
brew install redis
brew services start redis      # runs in background
# or run in foreground: redis-server
```

**Ubuntu/Debian**
```bash
sudo apt update && sudo apt install redis-server
sudo systemctl enable --now redis-server
```

**Docker (any OS) — the easiest, no install**
```bash
docker run -d --name redis -p 6379:6379 redis:7
```

**Verify it's alive:**
```bash
redis-cli ping
# → PONG
```

If you see `PONG`, your broker is ready.

## Step 2 — Create the Django project

```bash
mkdir celery_demo && cd celery_demo
python -m venv .venv
source .venv/bin/activate            # Windows: .venv\Scripts\activate

pip install "django>=4.2" "celery>=5.3" "redis>=4.5"

django-admin startproject config .   # note the trailing dot
python manage.py startapp tasks_app
```

Add the app in `config/settings.py`:
```python
INSTALLED_APPS = [
    # ...
    "tasks_app",
]
```

Your layout:
```
celery_demo/
├── config/
│   ├── __init__.py
│   ├── settings.py
│   ├── celery.py        ← we create this next
│   └── ...
├── tasks_app/
│   ├── tasks.py         ← we create this in Lesson 03
│   └── ...
└── manage.py
```

## Step 3 — Create the Celery app (`config/celery.py`)

This is the standard Django + Celery bootstrap. Create `config/celery.py`:

```python
import os
from celery import Celery

# Tell Celery where Django's settings live (same as manage.py uses)
os.environ.setdefault("DJANGO_SETTINGS_MODULE", "config.settings")

# Create the Celery application instance
app = Celery("config")

# Read all CELERY_* settings from Django settings.py, using the "CELERY" namespace
app.config_from_object("django.conf:settings", namespace="CELERY")

# Auto-discover tasks.py in every installed app
app.autodiscover_tasks()


@app.task(bind=True, ignore_result=True)
def debug_task(self):
    print(f"Request: {self.request!r}")
```

## Step 4 — Load Celery when Django starts (`config/__init__.py`)

So that `shared_task` works everywhere, edit `config/__init__.py`:

```python
from .celery import app as celery_app

__all__ = ("celery_app",)
```

## Step 5 — Configure the broker & backend (`config/settings.py`)

Add to the bottom of `settings.py`:

```python
# --- Celery configuration ---
# Broker: where tasks wait. Required.
CELERY_BROKER_URL = "redis://localhost:6379/0"

# Result backend: where return values are stored. Optional — we enable it
# now so we can SEE results while learning. Disable in prod if unused.
CELERY_RESULT_BACKEND = "redis://localhost:6379/1"

# Only accept JSON — safe, readable, the modern default.
CELERY_ACCEPT_CONTENT = ["json"]
CELERY_TASK_SERIALIZER = "json"
CELERY_RESULT_SERIALIZER = "json"

# Use the timezone Django uses.
CELERY_TIMEZONE = "UTC"

# Helpful in development: see tracebacks and behave more predictably.
CELERY_TASK_TRACK_STARTED = True
```

> Note the two Redis **databases**: `/0` for the broker, `/1` for results.
> Redis has 16 numbered DBs (0–15); keeping them separate avoids key clashes.

## Step 6 — Start the worker

Open a **second terminal** (keep `runserver` for later in a third), activate the
venv, and run:

```bash
celery -A config worker --loglevel=info
```

You should see a startup banner ending with something like:
```
[config]
- ** ---------- .> transport:   redis://localhost:6379/0
- ** ---------- .> results:     redis://localhost:6379/1
[tasks]
  . config.celery.debug_task
celery@yourhost ready.
```

🎉 `ready.` means the worker connected to Redis and is waiting for tasks.

> **On Windows:** the default worker pool can misbehave. If tasks hang, run:
> `celery -A config worker --loglevel=info --pool=solo`

## Common setup errors (and fixes)

| Error | Cause | Fix |
|-------|-------|-----|
| `Connection refused` to 6379 | Redis isn't running | Start Redis; `redis-cli ping` |
| `No module named 'config'` | Wrong `-A` name | Use your project package name |
| Worker starts but sees no tasks | `autodiscover` / app not in INSTALLED_APPS | Add app, put tasks in `tasks.py` |
| Tasks queue but never run | Worker not started, or wrong broker URL | Start worker; check both URLs match |

## Exercise

Get the worker to print `ready.`. Then, in yet another terminal, run the Django
shell and trigger the debug task:

```bash
python manage.py shell
```
```python
from config.celery import debug_task
debug_task.delay()
```

Watch your worker terminal — you should see it receive and run `debug_task`.
If you see that, your whole pipeline works. If not, use the table above.

Next → [Lesson 03: Your first task](03-first-task.md)
