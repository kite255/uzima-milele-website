# Uzima Milele Website

Laravel application for Uzima Milele lessons, devotions, subscribers, and email campaigns.

## Email campaign production settings

Advanced email campaigns continue to use cPanel SMTP and the Laravel database queue.

Recommended production environment values:

```env
QUEUE_CONNECTION=database
MAIL_CAMPAIGN_BATCH_SIZE=20
MAIL_CAMPAIGN_BATCH_DELAY_MINUTES=10
```

The scheduler and queue worker should run with PHP 8.3 in production.

Campaign sending is intentionally throttled to 20 recipients per batch with a 10-minute delay between batches unless an environment-specific override is deliberately configured.

## Delivery terminology

With the current cPanel SMTP integration, a message accepted by SMTP is recorded as **Sent**. It must not be described as confirmed mailbox delivery. The application does not currently receive a provider delivery webhook that can prove final mailbox delivery, bounce, or complaint status.

Open tracking and click tracking are application-level signals and are separate from confirmed delivery status.

## Advanced email campaign rollout checklist

Before production promotion:

- run the targeted email and advanced campaign test suites;
- run the full Laravel test suite;
- verify all advanced campaign migrations have been applied;
- verify `QUEUE_CONNECTION=database`;
- verify `MAIL_CAMPAIGN_BATCH_SIZE=20`;
- verify `MAIL_CAMPAIGN_BATCH_DELAY_MINUTES=10`;
- confirm scheduler and queue-worker cron entries use PHP 8.3;
- validate a controlled staging campaign of about 25 recipients;
- confirm pause, resume, cancel, suppression, open tracking, click tracking, resend-to-non-openers, retry-failed, test-send, analytics, and CSV export behavior;
- perform a small controlled production campaign before any large send.
