# Lesson 10 — Capstone project

Time to put it all together. You'll build a small but realistic feature that
uses almost everything in this course. No new concepts — just assembly.

## The brief: "Bulk Newsletter Sender with reports"

Build a Django feature where an admin uploads a CSV of subscribers and triggers a
newsletter send. The system must:

1. Accept the upload **without making the admin wait** for the send.
2. Validate and import subscribers in the background.
3. Send emails **in parallel**, with **retries** for transient failures.
4. After all sends finish, generate a **summary report** (sent / failed counts).
5. Run a **nightly cleanup** of old import files.
6. Let the admin **check progress** via a status endpoint.

This exercises: views firing tasks, id-passing, groups + chord, retries &
idempotency, beat scheduling, and status polling.

## Suggested data model

```python
# models.py
class Campaign(models.Model):
    name = models.CharField(max_length=200)
    created_at = models.DateTimeField(auto_now_add=True)
    status = models.CharField(max_length=20, default="pending")  # pending/sending/done
    sent_count = models.IntegerField(default=0)
    failed_count = models.IntegerField(default=0)

class Subscriber(models.Model):
    campaign = models.ForeignKey(Campaign, on_delete=models.CASCADE)
    email = models.EmailField()
    sent = models.BooleanField(default=False)        # for idempotency!
    class Meta:
        unique_together = ("campaign", "email")      # de-dupe defense
```

## Architecture

```
POST /campaigns/  (CSV upload)
   │  save file, create Campaign(status="pending"), return campaign_id instantly
   ▼
import_subscribers.delay(campaign_id, file_path)        # background import
   │  parses CSV, bulk-creates Subscribers (idempotent)
   ▼
chord(
   group(send_one.s(sub_id) for each subscriber),       # parallel sends + retries
   finalize_campaign.s(campaign_id),                    # runs after all finish
)
   ▼
finalize_campaign  → set status="done", tally counts

Beat: cleanup_old_uploads  every night at 03:00
GET /campaigns/<id>/status/ → JSON progress
```

## Tasks to implement

```python
from celery import shared_task, chord, group
from celery.utils.log import get_task_logger

logger = get_task_logger(__name__)


@shared_task
def import_subscribers(campaign_id, file_path):
    campaign = Campaign.objects.get(pk=campaign_id)
    campaign.status = "sending"
    campaign.save(update_fields=["status"])

    emails = read_emails_from_csv(file_path)            # you write this helper
    for email in emails:
        Subscriber.objects.get_or_create(               # idempotent import
            campaign=campaign, email=email
        )

    sub_ids = list(
        Subscriber.objects.filter(campaign=campaign, sent=False)
                          .values_list("id", flat=True)
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
        return "already sent"
    send_actual_email(sub.email)                         # your email function
    sub.sent = True
    sub.save(update_fields=["sent"])
    return "sent"


@shared_task
def finalize_campaign(results, campaign_id):
    # `results` is the list of return values from every send_one
    campaign = Campaign.objects.get(pk=campaign_id)
    campaign.sent_count = Subscriber.objects.filter(
        campaign=campaign, sent=True).count()
    campaign.failed_count = Subscriber.objects.filter(
        campaign=campaign, sent=False).count()
    campaign.status = "done"
    campaign.save()
    logger.info("Campaign %s done: %s sent, %s failed",
                campaign_id, campaign.sent_count, campaign.failed_count)


@shared_task
def cleanup_old_uploads():
    # delete CSV files older than 7 days
    ...
```

## Views

```python
def create_campaign(request):
    file_path = save_uploaded_file(request.FILES["csv"])   # you write this
    campaign = Campaign.objects.create(name=request.POST["name"])
    import_subscribers.delay(campaign.id, file_path)       # fire & return
    return JsonResponse({"campaign_id": campaign.id, "status": "queued"})

def campaign_status(request, campaign_id):
    c = Campaign.objects.get(pk=campaign_id)
    return JsonResponse({
        "status": c.status,
        "sent": c.sent_count,
        "failed": c.failed_count,
    })
```

## Beat schedule

```python
CELERY_BEAT_SCHEDULE = {
    "cleanup-old-uploads": {
        "task": "tasks_app.tasks.cleanup_old_uploads",
        "schedule": crontab(hour=3, minute=0),
    },
}
```

## Running the whole thing

```bash
# 1. Redis
redis-cli ping
# 2. Worker
celery -A config worker -l info
# 3. Beat
celery -A config beat -l info
# 4. Flower (watch it work)
celery -A config flower
# 5. Django
python manage.py runserver
```

Upload a CSV → get a `campaign_id` instantly → poll `/campaigns/<id>/status/` →
watch counts climb in real time while Flower shows the parallel sends.

## Self-check — did you apply each lesson?

- [ ] View returns **immediately**; no `.get()` in the request path. (L00, L03)
- [ ] Tasks receive **ids**, re-fetch inside. (L04)
- [ ] `send_one` is **idempotent** (`sent` guard + `get_or_create` + unique). (L05)
- [ ] Retries with backoff on `SMTPException`; `acks_late=True`. (L05)
- [ ] **Group** to parallelize sends, **chord** to finalize after. (L07)
- [ ] **Beat** schedules nightly cleanup. (L06)
- [ ] You watched it in **Flower** and read worker logs. (L08)
- [ ] (Bonus) Sends routed to their own **queue**. (L09)

## Stretch goals

- Add a `fast`/`slow` queue split and route sends to a dedicated worker.
- Add `link_error` to record per-send failures into a `FailedSend` table and
  alert if failure rate > 10%.
- Replace polling with WebSockets (Django Channels) to push live progress.
- Add a Redis lock so the nightly cleanup can never overlap.

## You're done 🎉

You can now design, build, schedule, secure, and debug background processing in
Django with Celery + Redis. Re-read [Lesson 09](09-production-and-gotchas.md)
before any real deploy, and keep Flower open whenever you're developing tasks.

← Back to the [course index](README.md)
