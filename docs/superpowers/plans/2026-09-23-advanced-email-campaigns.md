# Advanced Email Campaigns Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Upgrade Uzima Milele's existing campaign module into a safer, segmented, trackable campaign system while continuing to use cPanel SMTP and the existing database queue.

**Architecture:** Keep Eloquent models as the source of truth, but move behavior into focused campaign services: audience selection, suppression, sending, personalization, tracking, analytics, and audit logging. Preserve existing campaigns, subscriber tokens, open tracking, and current batching while adding additive schema changes and provider-ready extension points.

**Tech Stack:** Laravel 12, PHP 8.3, Filament, MySQL/MariaDB, database queue, Blade mail views, PHPUnit/Laravel feature tests.

**Spec:** `docs/superpowers/specs/2026-09-23-advanced-email-campaigns-design.md`

## Global Constraints

- Continue using cPanel SMTP for this release.
- Keep the database queue as the queue driver in production.
- Preserve production throttling defaults: 20 recipients per batch and 10 minutes between batches.
- Do not label SMTP acceptance as `Delivered`; use `Sent`.
- Existing historical campaign status `sent` remains valid and is treated as completed history.
- Existing recipient states `pending`, `sent`, and `failed` remain valid.
- Existing subscriber `unsubscribe_token` values remain valid.
- Existing open-tracking tokens and open-tracking fields remain valid.
- New schema changes must be additive and backward-compatible.
- Editable campaign placeholders standardize on `{{...}}`; existing Blade templates continue using normal Blade variables internally.
- Never silently correct suspicious email addresses.
- Re-check subscription and suppression eligibility immediately before every individual send.

## Review Focus

- A subscriber who unsubscribes after the recipient snapshot is created must still be suppressed before send.
- Two workers trying to process the same campaign must not send the same pending recipients twice.
- A `No tracked open` segment must exclude unsubscribed/suppressed subscribers even when they qualified in the original campaign.
- A malformed or tampered click-tracking URL must not expose subscriber data or redirect to an unsafe destination.
- Public re-subscription may clear only unsubscribe-origin suppression; manual, repeated-failure, bounce, or complaint suppression must remain.

---

### Task 1: Add the advanced campaign schema and model contracts

**Files:**
- Create: `database/migrations/2026_09_23_200001_add_advanced_fields_to_email_campaigns_table.php`
- Create: `database/migrations/2026_09_23_200002_add_advanced_fields_to_email_campaign_recipients_table.php`
- Create: `database/migrations/2026_09_23_200003_create_email_suppressions_table.php`
- Create: `database/migrations/2026_09_23_200004_create_email_campaign_activity_logs_table.php`
- Create: `database/migrations/2026_09_23_200005_create_email_campaign_templates_table.php`
- Create: `database/migrations/2026_09_23_200006_create_email_campaign_clicks_table.php`
- Create: `app/Models/EmailSuppression.php`
- Create: `app/Models/EmailCampaignActivityLog.php`
- Create: `app/Models/EmailCampaignTemplate.php`
- Create: `app/Models/EmailCampaignClick.php`
- Modify: `app/Models/EmailCampaign.php`
- Modify: `app/Models/EmailCampaignRecipient.php`
- Modify: `app/Models/EmailSubscriber.php`
- Test: `tests/Feature/AdvancedEmailCampaignSchemaTest.php`

**Interfaces:**
- Produces campaign statuses `paused`, `completed`, `cancelled` in addition to existing values.
- Produces recipient status `suppressed`.
- Produces relationships `EmailCampaign::activityLogs()`, `template()`, `parentCampaign()`, `childCampaigns()`, `clicks()`.
- Produces relationships `EmailCampaignRecipient::clicks()` and `EmailSubscriber::suppressions()`.

- [ ] **Step 1: Write the failing schema/model test**

```php
public function test_advanced_email_campaign_schema_exists(): void
{
    $this->assertTrue(Schema::hasColumns('email_campaigns', [
        'paused_at', 'cancelled_at', 'completed_at', 'parent_campaign_id',
        'audience_filter_type', 'audience_filter_value', 'template_id',
        'last_batch_sent_at',
    ]));

    $this->assertTrue(Schema::hasColumns('email_campaign_recipients', [
        'suppressed_at', 'suppression_reason', 'first_clicked_at',
        'last_clicked_at', 'click_count', 'unsubscribed_at', 'failure_count',
    ]));

    $this->assertTrue(Schema::hasTable('email_suppressions'));
    $this->assertTrue(Schema::hasTable('email_campaign_activity_logs'));
    $this->assertTrue(Schema::hasTable('email_campaign_templates'));
    $this->assertTrue(Schema::hasTable('email_campaign_clicks'));
}
```

- [ ] **Step 2: Run the test and verify it fails**

Run: `php artisan test --filter=AdvancedEmailCampaignSchemaTest`
Expected: FAIL because the new columns/tables do not exist.

- [ ] **Step 3: Add migrations and model constants/casts/relationships**

Use these exact new constants:

```php
// EmailCampaign
public const STATUS_PAUSED = 'paused';
public const STATUS_COMPLETED = 'completed';
public const STATUS_CANCELLED = 'cancelled';

// EmailCampaignRecipient
public const STATUS_SUPPRESSED = 'suppressed';
```

Add nullable timestamps and JSON/text audience filter storage. `email_suppressions.email` must be unique and normalized to lowercase. `email_campaign_clicks` must use foreign keys to campaign and recipient and store `url`, `clicked_at`, nullable `user_agent`, nullable `ip_hash`.

- [ ] **Step 4: Run the schema test**

Run: `php artisan test --filter=AdvancedEmailCampaignSchemaTest`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add database/migrations app/Models tests/Feature/AdvancedEmailCampaignSchemaTest.php
git commit -m "feat: add advanced campaign schema"
```

---

### Task 2: Implement suppression and unsubscribe safety

**Files:**
- Create: `app/Services/Email/CampaignSuppressionService.php`
- Modify: `app/Http/Controllers/EmailSubscriberController.php`
- Modify: `routes/web.php`
- Modify: `app/Models/EmailSubscriber.php`
- Test: `tests/Feature/EmailSuppressionTest.php`
- Test: `tests/Feature/PublicEmailSubscriptionTest.php`

**Interfaces:**
- Produces `CampaignSuppressionService::isSuppressed(string $email): bool`.
- Produces `CampaignSuppressionService::suppress(string $email, string $reason, string $source, ?string $notes = null, ?int $createdBy = null): EmailSuppression`.
- Produces `CampaignSuppressionService::removeUnsubscribeSuppression(string $email): void`.
- Produces `CampaignSuppressionService::canReceive(EmailSubscriber $subscriber): bool`.

- [ ] **Step 1: Write failing suppression tests**

```php
public function test_unsubscribed_subscriber_is_suppressed(): void
{
    $subscriber = EmailSubscriber::factory()->create([
        'status' => 'subscribed',
        'email' => 'person@example.com',
    ]);

    app(CampaignSuppressionService::class)->suppress(
        $subscriber->email,
        'unsubscribed',
        'public_unsubscribe'
    );

    $this->assertFalse(
        app(CampaignSuppressionService::class)->canReceive($subscriber)
    );
}

public function test_public_resubscribe_removes_only_unsubscribe_suppression(): void
{
    // Create both unsubscribe and manual suppressions in separate subscribers/emails.
    // Re-subscribe and assert only the unsubscribe-origin suppression is removed.
}
```

- [ ] **Step 2: Run suppression tests and verify failure**

Run: `php artisan test --filter=EmailSuppressionTest`
Expected: FAIL because the service does not exist.

- [ ] **Step 3: Implement suppression service and unsubscribe/resubscribe integration**

Eligibility rule must be:

```php
public function canReceive(EmailSubscriber $subscriber): bool
{
    return $subscriber->status === 'subscribed'
        && filter_var($subscriber->email, FILTER_VALIDATE_EMAIL)
        && ! $this->isSuppressed($subscriber->email);
}
```

The existing unsubscribe route must set `status=unsubscribed`, set `unsubscribed_at`, and create suppression reason `unsubscribed`. Public re-subscribe must remove only an `unsubscribed` suppression for that email.

- [ ] **Step 4: Run suppression and existing subscription tests**

Run: `php artisan test --filter='EmailSuppressionTest|PublicEmailSubscriptionTest'`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Services/Email app/Http/Controllers/EmailSubscriberController.php app/Models/EmailSubscriber.php routes/web.php tests/Feature/EmailSuppressionTest.php tests/Feature/PublicEmailSubscriptionTest.php
git commit -m "feat: add campaign suppression safety"
```

---

### Task 3: Implement advanced audience segmentation

**Files:**
- Create: `app/Services/Email/CampaignAudienceService.php`
- Modify: `app/Services/EmailCampaignService.php`
- Modify: `app/Models/EmailCampaign.php`
- Test: `tests/Feature/EmailCampaignAdvancedAudienceTest.php`

**Interfaces:**
- Produces `CampaignAudienceService::subscriberQuery(EmailCampaign $campaign): Builder`.
- Produces `CampaignAudienceService::snapshot(EmailCampaign $campaign): int` returning the number of recipients created.
- Consumes `CampaignSuppressionService::canReceive()`.

- [ ] **Step 1: Write failing audience tests**

Cover these filter values exactly:

```text
new_7_days
new_30_days
subscribed_after
never_received
never_opened
opened_campaign
not_opened_campaign
clicked_campaign
not_clicked_campaign
inactive_30_days
inactive_60_days
inactive_90_days
```

Example:

```php
public function test_not_opened_selected_campaign_excludes_suppressed_subscribers(): void
{
    // Create original campaign recipient with STATUS_SENT and first_opened_at = null.
    // Suppress subscriber.
    // Build new campaign with audience_filter_type = not_opened_campaign.
    // Assert subscriber is absent from snapshot.
}
```

- [ ] **Step 2: Run and verify failure**

Run: `php artisan test --filter=EmailCampaignAdvancedAudienceTest`
Expected: FAIL because the audience service does not exist.

- [ ] **Step 3: Implement query builders and snapshot creation**

Every segment query must end with active-subscriber filtering. The snapshot method must deduplicate by normalized email and create `EmailCampaignRecipient` rows with `STATUS_PENDING` only for eligible candidates.

- [ ] **Step 4: Run new and existing audience tests**

Run: `php artisan test --filter='EmailCampaignAdvancedAudienceTest|EmailCampaignAudienceSelectionTest|EmailCampaignGroupAudienceTest'`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Services/Email/CampaignAudienceService.php app/Services/EmailCampaignService.php app/Models/EmailCampaign.php tests/Feature/EmailCampaignAdvancedAudienceTest.php
git commit -m "feat: add advanced campaign audiences"
```

---

### Task 4: Refactor batch sending into CampaignSendingService with pause, resume, cancel, retry, and locking

**Files:**
- Create: `app/Services/Email/CampaignSendingService.php`
- Modify: `app/Jobs/SendEmailCampaign.php`
- Modify: `app/Models/EmailCampaign.php`
- Modify: `app/Models/EmailCampaignRecipient.php`
- Test: `tests/Feature/EmailCampaignSendingControlsTest.php`
- Modify: `tests/Feature/EmailCampaignBatchSendingTest.php`

**Interfaces:**
- Produces `queue(EmailCampaign $campaign): void`.
- Produces `processBatch(int $campaignId): void`.
- Produces `pause(EmailCampaign $campaign): void`.
- Produces `resume(EmailCampaign $campaign): void`.
- Produces `cancel(EmailCampaign $campaign): void`.
- Produces `createRetryDraft(EmailCampaign $campaign, ?int $createdBy = null): EmailCampaign`.
- Consumes `CampaignSuppressionService` and `CampaignAuditService` from Task 8; until Task 8 exists, call a protected no-op method `audit()` in this service and replace it in Task 8.

- [ ] **Step 1: Write failing sending-control tests**

```php
public function test_paused_campaign_does_not_process_next_batch(): void
{
    $campaign = $this->campaignWithPendingRecipients(25, [
        'status' => EmailCampaign::STATUS_PAUSED,
    ]);

    app(CampaignSendingService::class)->processBatch($campaign->id);

    $this->assertSame(25, $campaign->pendingRecipients()->count());
    Mail::assertNothingSent();
}

public function test_send_time_recheck_suppresses_recently_unsubscribed_recipient(): void
{
    // Snapshot while subscribed, then unsubscribe before processBatch().
    // Assert no mail is sent and recipient becomes STATUS_SUPPRESSED.
}
```

Add a concurrency test using Laravel cache/database locking that invokes the same campaign batch twice and asserts each recipient is sent at most once.

- [ ] **Step 2: Run and verify failure**

Run: `php artisan test --filter='EmailCampaignSendingControlsTest|EmailCampaignBatchSendingTest'`
Expected: FAIL.

- [ ] **Step 3: Implement service and make the job a thin delegator**

`SendEmailCampaign::handle()` must become:

```php
public function handle(CampaignSendingService $service): void
{
    $service->processBatch($this->campaignId);
}
```

Acquire a per-campaign lock before loading pending recipients. Check campaign status before batch work. Re-check suppression for each recipient immediately before send. Keep the current config keys `mail.campaign_batch_size` and `mail.campaign_batch_delay_minutes`.

- [ ] **Step 4: Run sending tests**

Run: `php artisan test --filter='EmailCampaignSendingControlsTest|EmailCampaignBatchSendingTest'`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Services/Email/CampaignSendingService.php app/Jobs/SendEmailCampaign.php app/Models/EmailCampaign.php app/Models/EmailCampaignRecipient.php tests/Feature/EmailCampaignSendingControlsTest.php tests/Feature/EmailCampaignBatchSendingTest.php
git commit -m "feat: add campaign sending controls"
```

---

### Task 5: Add personalization, preview rendering, and test sending

**Files:**
- Create: `app/Services/Email/CampaignPersonalizationService.php`
- Create: `app/Services/Email/CampaignPreviewService.php`
- Create: `app/Mail/CampaignTestMail.php`
- Modify: `app/Mail/CustomCampaignMail.php`
- Modify: `app/Mail/DevotionCampaignMail.php`
- Modify: `resources/views/emails/campaigns/custom.blade.php`
- Test: `tests/Feature/EmailCampaignPersonalizationTest.php`
- Test: `tests/Feature/EmailCampaignTestSendTest.php`

**Interfaces:**
- Produces `CampaignPersonalizationService::render(string $content, EmailCampaign $campaign, ?EmailCampaignRecipient $recipient = null, ?EmailSubscriber $subscriber = null): string`.
- Produces `CampaignPersonalizationService::variables(...): array`.
- Produces `CampaignPreviewService::render(EmailCampaign $campaign): string`.
- Produces `CampaignPreviewService::sendTest(EmailCampaign $campaign, array $emails): void`.

- [ ] **Step 1: Write failing personalization tests**

```php
public function test_supported_placeholders_render_with_safe_fallbacks(): void
{
    $content = 'Habari {{first_name}} - {{email}} - {{campaign_name}}';

    $rendered = app(CampaignPersonalizationService::class)->render(
        $content,
        $campaign,
        $recipient,
        $subscriber
    );

    $this->assertStringNotContainsString('{{', $rendered);
    $this->assertStringContainsString($subscriber->email, $rendered);
}
```

Explicitly test all supported placeholders from the spec and verify unknown placeholders remain escaped/unchanged rather than executed.

- [ ] **Step 2: Run and verify failure**

Run: `php artisan test --filter='EmailCampaignPersonalizationTest|EmailCampaignTestSendTest'`
Expected: FAIL.

- [ ] **Step 3: Implement controlled placeholder replacement**

Supported keys must be exactly:

```php
[
    'first_name', 'last_name', 'name', 'email', 'language',
    'campaign_name', 'campaign_subject', 'devotion_title',
    'devotion_url', 'unsubscribe_url',
]
```

Do not evaluate PHP, Blade, or arbitrary expressions from campaign content. Test sends must not create `email_campaign_recipients` rows and must not increment analytics counters.

- [ ] **Step 4: Run mail rendering tests**

Run: `php artisan test --filter='EmailCampaignPersonalizationTest|EmailCampaignTestSendTest|EmailCampaignOpenTrackingTest'`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Services/Email/CampaignPersonalizationService.php app/Services/Email/CampaignPreviewService.php app/Mail resources/views/emails/campaigns/custom.blade.php tests/Feature/EmailCampaignPersonalizationTest.php tests/Feature/EmailCampaignTestSendTest.php
git commit -m "feat: add campaign personalization and previews"
```

---

### Task 6: Add click tracking with safe redirects

**Files:**
- Create: `app/Services/Email/CampaignTrackingService.php`
- Create: `app/Http/Controllers/EmailCampaignClickController.php`
- Modify: `routes/web.php`
- Modify: `app/Models/EmailCampaignRecipient.php`
- Modify: `resources/views/emails/campaigns/custom.blade.php`
- Modify: `resources/views/emails/devotions/daily.blade.php`
- Test: `tests/Feature/EmailCampaignClickTrackingTest.php`

**Interfaces:**
- Produces `CampaignTrackingService::trackableUrl(EmailCampaignRecipient $recipient, string $destination): string`.
- Produces `CampaignTrackingService::recordClick(EmailCampaignRecipient $recipient, string $destination, ?string $userAgent, ?string $ip): EmailCampaignClick`.

- [ ] **Step 1: Write failing click tests**

```php
public function test_valid_click_is_recorded_and_redirects(): void
{
    $url = app(CampaignTrackingService::class)->trackableUrl(
        $recipient,
        'https://uzimamilele.or.tz/tafakari/example'
    );

    $response = $this->get($url);

    $response->assertRedirect('https://uzimamilele.or.tz/tafakari/example');
    $this->assertDatabaseCount('email_campaign_clicks', 1);
}

public function test_tampered_click_url_fails_without_exposing_recipient(): void
{
    $response = $this->get('/email/click/invalid-token');
    $response->assertNotFound();
}
```

- [ ] **Step 2: Run and verify failure**

Run: `php artisan test --filter=EmailCampaignClickTrackingTest`
Expected: FAIL.

- [ ] **Step 3: Implement signed tracking and link rewriting**

Only allow `http` and `https` destinations. Reject `javascript:`, `data:`, malformed, or missing destinations. Store `hash('sha256', $ip)` rather than raw IP. Update `first_clicked_at`, `last_clicked_at`, and `click_count` transactionally.

- [ ] **Step 4: Run click and open tracking tests**

Run: `php artisan test --filter='EmailCampaignClickTrackingTest|EmailCampaignOpenTrackingTest'`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Services/Email/CampaignTrackingService.php app/Http/Controllers/EmailCampaignClickController.php app/Models/EmailCampaignRecipient.php resources/views/emails routes/web.php tests/Feature/EmailCampaignClickTrackingTest.php
git commit -m "feat: add campaign click tracking"
```

---

### Task 7: Add reusable campaign templates and duplication/resend actions

**Files:**
- Create: `app/Services/Email/CampaignTemplateService.php`
- Create: `app/Services/Email/CampaignCloneService.php`
- Create: `app/Filament/Resources/EmailCampaignTemplateResource.php`
- Create: `app/Filament/Resources/EmailCampaignTemplateResource/Pages/ListEmailCampaignTemplates.php`
- Create: `app/Filament/Resources/EmailCampaignTemplateResource/Pages/CreateEmailCampaignTemplate.php`
- Create: `app/Filament/Resources/EmailCampaignTemplateResource/Pages/EditEmailCampaignTemplate.php`
- Modify: `app/Filament/Resources/EmailCampaignResource.php`
- Test: `tests/Feature/EmailCampaignTemplateTest.php`
- Test: `tests/Feature/EmailCampaignCloneActionsTest.php`

**Interfaces:**
- Produces `CampaignTemplateService::apply(EmailCampaign $campaign, EmailCampaignTemplate $template): void`.
- Produces `CampaignCloneService::duplicate(EmailCampaign $source, ?int $createdBy = null): EmailCampaign`.
- Produces `CampaignCloneService::resendToNonOpeners(EmailCampaign $source, ?int $createdBy = null): EmailCampaign`.
- Produces `CampaignCloneService::retryFailed(EmailCampaign $source, ?int $createdBy = null): EmailCampaign`.

- [ ] **Step 1: Write failing template/clone tests**

Ensure duplicated campaigns copy content/config but no recipients/counters/timestamps. Ensure resend-to-non-openers includes only `STATUS_SENT` recipients with `first_opened_at=null` whose subscriber is still eligible.

- [ ] **Step 2: Run and verify failure**

Run: `php artisan test --filter='EmailCampaignTemplateTest|EmailCampaignCloneActionsTest'`
Expected: FAIL.

- [ ] **Step 3: Implement services and Filament template resource**

Template types must be constrained to: `devotion`, `children_devotion`, `newsletter`, `announcement`, `lesson_reminder`, `special_event`, `general`.

- [ ] **Step 4: Run tests**

Run: `php artisan test --filter='EmailCampaignTemplateTest|EmailCampaignCloneActionsTest'`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Services/Email/CampaignTemplateService.php app/Services/Email/CampaignCloneService.php app/Filament/Resources/EmailCampaignTemplateResource.php app/Filament/Resources/EmailCampaignTemplateResource app/Filament/Resources/EmailCampaignResource.php tests/Feature/EmailCampaignTemplateTest.php tests/Feature/EmailCampaignCloneActionsTest.php
git commit -m "feat: add campaign templates and clone actions"
```

---

### Task 8: Add analytics, activity logging, and CSV export

**Files:**
- Create: `app/Services/Email/CampaignAnalyticsService.php`
- Create: `app/Services/Email/CampaignAuditService.php`
- Create: `app/Services/Email/CampaignExportService.php`
- Modify: `app/Services/Email/CampaignSendingService.php`
- Modify: `app/Models/EmailCampaign.php`
- Test: `tests/Feature/EmailCampaignAnalyticsTest.php`
- Test: `tests/Feature/EmailCampaignAuditTest.php`
- Test: `tests/Feature/EmailCampaignExportTest.php`

**Interfaces:**
- Produces `CampaignAnalyticsService::summary(EmailCampaign $campaign): array`.
- Produces `CampaignAnalyticsService::audienceHealth(): array`.
- Produces `CampaignAuditService::record(EmailCampaign $campaign, string $action, ?string $description = null, array $metadata = [], ?int $performedBy = null): EmailCampaignActivityLog`.
- Produces `CampaignExportService::csv(EmailCampaign $campaign): StreamedResponse`.

- [ ] **Step 1: Write failing analytics/audit/export tests**

Analytics summary keys must be exactly:

```php
[
    'total_recipients', 'sent', 'pending', 'failed', 'suppressed',
    'opened', 'no_tracked_open', 'clicked', 'unsubscribed',
    'send_success_rate', 'open_rate', 'click_rate',
    'click_to_open_rate', 'unsubscribe_rate',
]
```

- [ ] **Step 2: Run and verify failure**

Run: `php artisan test --filter='EmailCampaignAnalyticsTest|EmailCampaignAuditTest|EmailCampaignExportTest'`
Expected: FAIL.

- [ ] **Step 3: Implement services and wire sending lifecycle audit events**

Record at minimum: `created`, `scheduled`, `queued`, `started`, `batch_processed`, `paused`, `resumed`, `cancelled`, `completed`, `retry_failed`, `resend_non_openers`, `duplicated`, `test_email_sent`.

- [ ] **Step 4: Run tests**

Run: `php artisan test --filter='EmailCampaignAnalyticsTest|EmailCampaignAuditTest|EmailCampaignExportTest'`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Services/Email/CampaignAnalyticsService.php app/Services/Email/CampaignAuditService.php app/Services/Email/CampaignExportService.php app/Services/Email/CampaignSendingService.php app/Models/EmailCampaign.php tests/Feature/EmailCampaignAnalyticsTest.php tests/Feature/EmailCampaignAuditTest.php tests/Feature/EmailCampaignExportTest.php
git commit -m "feat: add campaign analytics and audit trail"
```

---

### Task 9: Upgrade Filament campaign UX and subscriber hygiene

**Files:**
- Modify: `app/Filament/Resources/EmailCampaignResource.php`
- Modify: `app/Filament/Resources/EmailCampaignResource/Pages/*`
- Modify: `app/Filament/Resources/EmailSubscriberResource.php`
- Create: `app/Services/Email/EmailAddressHygieneService.php`
- Test: `tests/Feature/EmailCampaignFilamentActionsTest.php`
- Test: `tests/Feature/EmailAddressHygieneTest.php`

**Interfaces:**
- Produces `EmailAddressHygieneService::suggestion(string $email): ?string`.
- Consumes services from Tasks 2-8 for all Filament actions.

- [ ] **Step 1: Write failing Filament/action tests**

Assert contextual actions by state:

```text
Draft: Preview, Send Test, Schedule, Send Now, Duplicate
Scheduled: Preview, Cancel Schedule, Duplicate
Sending: Pause, Cancel
Paused: Resume, Cancel
Completed/Sent: Resend to Non-openers, Retry Failed, Duplicate, Export
Failed: Retry Failed, Duplicate
```

Add hygiene tests for `gmal.com`, `gmial.com`, and `yaho.com`; assert the service suggests but never mutates the email.

- [ ] **Step 2: Run and verify failure**

Run: `php artisan test --filter='EmailCampaignFilamentActionsTest|EmailAddressHygieneTest'`
Expected: FAIL.

- [ ] **Step 3: Refactor the oversized resource into focused schema/action helpers if needed**

If `EmailCampaignResource.php` remains unwieldy, extract only campaign-specific helpers into:

```text
app/Filament/Resources/EmailCampaignResource/Schemas/EmailCampaignForm.php
app/Filament/Resources/EmailCampaignResource/Tables/EmailCampaignTable.php
app/Filament/Resources/EmailCampaignResource/Actions/EmailCampaignActions.php
```

Keep user-facing sections in this order: Campaign Details, Content & Template, Audience, Preview & Test, Schedule & Send.

- [ ] **Step 4: Run Filament and email resource tests**

Run: `php artisan test --filter='EmailCampaignFilamentActionsTest|EmailAddressHygieneTest|EmailSubscriberResourceTest|EmailSettingsPageTest'`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Filament/Resources/EmailCampaignResource.php app/Filament/Resources/EmailCampaignResource app/Filament/Resources/EmailSubscriberResource.php app/Services/Email/EmailAddressHygieneService.php tests/Feature/EmailCampaignFilamentActionsTest.php tests/Feature/EmailAddressHygieneTest.php
git commit -m "feat: upgrade campaign admin experience"
```

---

### Task 10: Backward compatibility, authorization, integration verification, and rollout readiness

**Files:**
- Modify: `app/Models/EmailCampaign.php`
- Modify: `app/Filament/Resources/EmailCampaignResource.php`
- Modify: `routes/web.php`
- Create: `tests/Feature/AdvancedEmailCampaignAuthorizationTest.php`
- Create: `tests/Feature/AdvancedEmailCampaignBackwardCompatibilityTest.php`
- Create: `tests/Feature/AdvancedEmailCampaignIntegrationTest.php`
- Modify: `.env.example`
- Modify: `README.md`

**Interfaces:**
- Final integrated behavior across all prior tasks.

- [ ] **Step 1: Write failing compatibility and authorization tests**

Cover:

```php
public function test_historical_sent_campaign_is_treated_as_completed(): void
{
    $campaign = EmailCampaign::factory()->create(['status' => 'sent']);
    $this->assertTrue($campaign->isCompletedHistory());
}

public function test_public_tracking_and_unsubscribe_routes_are_public_but_admin_actions_require_admin(): void
{
    // Assert open/click/unsubscribe work without admin session.
    // Assert pause/resume/cancel/template management cannot be invoked by non-admin users.
}
```

- [ ] **Step 2: Run compatibility tests and verify failure**

Run: `php artisan test --filter='AdvancedEmailCampaignAuthorizationTest|AdvancedEmailCampaignBackwardCompatibilityTest|AdvancedEmailCampaignIntegrationTest'`
Expected: FAIL until compatibility helpers and authorization are complete.

- [ ] **Step 3: Implement compatibility helpers, authorization, docs, and environment documentation**

Document these production settings in `.env.example` and README without secrets:

```env
QUEUE_CONNECTION=database
MAIL_CAMPAIGN_BATCH_SIZE=20
MAIL_CAMPAIGN_BATCH_DELAY_MINUTES=10
```

Document that cPanel SMTP reports `Sent`, not confirmed mailbox delivery.

- [ ] **Step 4: Run targeted advanced campaign suite**

Run:

```bash
php artisan optimize:clear
php artisan test --filter=Email
php artisan test --filter=AdvancedEmailCampaign
```

Expected: all email and advanced campaign tests PASS.

- [ ] **Step 5: Run full application suite**

Run: `php artisan test`
Expected: full suite PASS with no regressions.

- [ ] **Step 6: Verify migration and queue configuration locally**

Run:

```bash
php artisan migrate:status --env=testing
php artisan tinker --env=testing --execute="dump(config('mail.campaign_batch_size'), config('mail.campaign_batch_delay_minutes'));"
```

Expected batch values: `20`, `10` unless explicitly overridden in testing.

- [ ] **Step 7: Commit final integration changes**

```bash
git add app routes tests .env.example README.md
git commit -m "feat: complete advanced email campaigns"
```

- [ ] **Step 8: Branch-level verification before staging merge**

Run:

```bash
git status
git log --oneline --decorate -10
php artisan test
```

Expected: clean working tree and full green suite.

## Staging Validation Checklist

After merging the feature branch to `staging`, use a dedicated `Campaign QA` subscriber group with about 25 controlled recipients.

- [ ] First batch processes no more than 20 recipients.
- [ ] Remaining recipients stay pending until the configured delay.
- [ ] Pause prevents the next batch.
- [ ] Resume safely queues the next batch once.
- [ ] Cancel stops all future batches.
- [ ] Unsubscribe immediately prevents a pending recipient from receiving mail.
- [ ] Manual suppression prevents send.
- [ ] Open tracking updates existing open fields.
- [ ] Click tracking records the click and redirects safely.
- [ ] Resend to non-openers creates a new draft and leaves the original unchanged.
- [ ] Retry failed creates a new draft and leaves original recipient history unchanged.
- [ ] Test email does not change campaign analytics.
- [ ] Campaign analytics use `Sent`, never `Delivered`.
- [ ] CSV export contains the expected recipient/report columns.

## Production Promotion Gate

Promote to `main` only after:

1. All targeted email tests pass.
2. Full application suite passes.
3. Staging QA checklist passes.
4. Production `.env` has `QUEUE_CONNECTION=database`.
5. Production scheduler and queue-worker cron entries use PHP 8.3.
6. Production config resolves `MAIL_CAMPAIGN_BATCH_SIZE=20` and `MAIL_CAMPAIGN_BATCH_DELAY_MINUTES=10`.
7. A small live campaign confirms controlled batching before any large send.
