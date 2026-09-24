# Advanced Email Campaigns Design

Date: 2026-09-23
Branch: `feature/advanced-email-campaigns`

## Goal

Upgrade the existing Uzima Milele email campaign module into a safer, more capable campaign system while continuing to use cPanel SMTP for sending.

The upgrade must preserve current campaign functionality, keep existing subscriber and open-tracking data valid, and make the system ready for a future provider with delivery/bounce webhooks without requiring another major redesign.

## Current Capabilities

The existing module already supports:

- Custom email campaigns
- Devotion-generated campaigns
- Draft, scheduled, queued, sending, sent, and failed campaign states
- All-subscriber, selected-subscriber, and subscriber-group audiences
- Subscriber import and management
- Open tracking
- Scheduled devotion email automation
- Database queue processing
- Campaign throttling using configurable batch size and delay
- Current production defaults of 20 recipients per batch and a 10-minute delay between batches

## Architecture

Use a modular service architecture instead of adding all new behavior directly to the Eloquent models.

### Core models

- `EmailCampaign`
- `EmailCampaignRecipient`
- `EmailSubscriber`
- `EmailSubscriberGroup`

### Services

#### CampaignAudienceService

Builds eligible recipient sets for:

- all active subscribers
- selected subscribers
- subscriber group
- new subscribers
- subscribers added in the last 7 or 30 days
- subscribers added after a selected date
- subscribers who have never received a campaign
- subscribers who never opened any campaign
- subscribers who opened a selected campaign
- subscribers with no tracked open for a selected campaign
- subscribers who clicked a selected campaign
- subscribers who did not click a selected campaign
- inactive subscribers for 30, 60, or 90 days

Audience selection only chooses candidates. Final eligibility is re-checked before sending.

#### CampaignSendingService

Responsible for:

- queueing campaigns
- processing batch sends
- pause
- resume
- cancel
- retry failed recipients
- final eligibility checks before each send
- ensuring suppressed or unsubscribed addresses are never sent
- preventing duplicate concurrent batch execution

#### CampaignSuppressionService

Responsible for:

- unsubscribed addresses
- manually suppressed addresses
- known invalid addresses
- repeated-failure suppression
- future bounce/complaint suppression support

#### CampaignTrackingService

Responsible for:

- open tracking
- click tracking
- recipient engagement timestamps and counters
- tracked-link redirect handling

#### CampaignPersonalizationService

Supports controlled placeholders such as:

- `{{first_name}}`
- `{{last_name}}`
- `{{name}}`
- `{{email}}`
- `{{language}}`
- `{{campaign_name}}`
- `{{campaign_subject}}`
- `{{devotion_title}}`
- `{{devotion_url}}`
- `{{unsubscribe_url}}`

Missing values must use a graceful fallback rather than exposing raw placeholders.

#### CampaignAnalyticsService

Calculates:

- total recipients
- sent
- pending
- failed
- suppressed
- opened
- no tracked open
- clicked
- unsubscribed
- send success rate
- open rate
- click rate
- click-to-open rate
- unsubscribe rate

The UI must not label SMTP acceptance as `Delivered` while using cPanel SMTP.

#### CampaignAuditService

Records campaign lifecycle and administrative events including:

- created
- updated
- scheduled
- queued
- started
- batch processed
- paused
- resumed
- cancelled
- completed
- retry failed
- resend to non-openers
- duplicated
- test email sent

## Campaign Lifecycle

New lifecycle states:

- `draft`
- `scheduled`
- `queued`
- `sending`
- `paused`
- `completed`
- `cancelled`
- `failed`

Existing historical `sent` campaigns remain valid and should be treated as completed for backward compatibility.

## Recipient State and Engagement

Delivery state remains separate from engagement.

Recipient delivery states:

- `pending`
- `sent`
- `failed`
- `suppressed`

Engagement data remains independent:

- `first_opened_at`
- `last_opened_at`
- `open_count`
- `first_clicked_at`
- `last_clicked_at`
- `click_count`
- `unsubscribed_at`

A recipient can therefore remain `sent` while also being opened, clicked, and later unsubscribed.

## Database Changes

### email_campaigns

Add fields such as:

- `paused_at`
- `cancelled_at`
- `completed_at`
- `parent_campaign_id`
- `audience_filter_type`
- `audience_filter_value`
- `template_id`
- `last_batch_sent_at`

### email_campaign_recipients

Add fields such as:

- `suppressed_at`
- `suppression_reason`
- `first_clicked_at`
- `last_clicked_at`
- `click_count`
- `unsubscribed_at`
- `failure_count`

### email_suppressions

Create a global suppression table containing:

- email
- reason
- source
- notes
- suppressed_at
- created_by
- timestamps

Supported reasons include:

- `unsubscribed`
- `manual`
- `invalid_address`
- `repeated_failure`
- `bounce` (future provider support)
- `complaint` (future provider support)

### email_campaign_activity_logs

Create an audit log table containing:

- email_campaign_id
- action
- description
- metadata
- performed_by
- created_at

### email_campaign_templates

Create reusable templates containing:

- name
- slug
- subject
- content
- type
- is_active
- created_by
- timestamps

### email_campaign_clicks

Create click event storage containing:

- email_campaign_id
- email_campaign_recipient_id
- url
- clicked_at
- user_agent
- ip_hash
- created_at

Raw IP addresses should not be stored unless there is a clear operational requirement.

## Subscriber Safety and Suppression

Before every individual send, the system must re-check:

- subscriber is still subscribed
- subscriber is not globally suppressed
- email format is valid

If any check fails, the recipient becomes `suppressed` and no email is sent.

This rule applies even when the campaign recipient snapshot was created earlier.

### Unsubscribe flow

Every bulk campaign email includes an unsubscribe URL based on the existing subscriber unsubscribe token.

Flow:

1. Recipient opens unsubscribe link.
2. System confirms the request.
3. Subscriber status becomes `unsubscribed`.
4. `unsubscribed_at` is stored.
5. A suppression entry is created.
6. Future campaigns exclude the address.

If a person explicitly re-subscribes through the public subscription form, an unsubscribe-origin suppression may be removed and the subscriber reactivated.

Manual, bounce, complaint, and other protective suppressions must not be removed automatically by public re-subscription.

## Audience Segmentation

Campaign creation should support:

- All active subscribers
- Selected subscribers
- Subscriber group
- New subscribers
- Subscribed in last 7 days
- Subscribed in last 30 days
- Subscribed after selected date
- Never received a campaign
- Never opened any campaign
- Opened selected campaign
- No tracked open for selected campaign
- Inactive 30 days
- Inactive 60 days
- Inactive 90 days
- Clicked selected campaign
- Did not click selected campaign

The system should label non-openers as `No tracked open`, because open pixels are not a perfect indicator that an email was unread.

## Resend to Non-openers

A completed campaign can create a new draft campaign containing recipients who:

- were sent the original campaign
- have no tracked open
- are still active subscribers
- are not suppressed

The original campaign must not be modified.

The new campaign should reference the original through `parent_campaign_id` and allow the administrator to modify the subject/content before sending.

## Retry Failed

Retrying failed recipients creates a new draft campaign containing only currently eligible failed recipients.

Do not reset the original campaign recipients back to pending.

## Pause, Resume, and Cancel

### Pause

- Allowed while sending.
- Current in-flight send may finish.
- No next batch should start.

### Resume

- Changes paused campaign back to sending.
- Queues the next batch safely.

### Cancel

- Stops future batches.
- Existing history remains unchanged.
- Pending recipients remain pending and are not processed further.

## Duplicate Campaign

Duplicating a campaign copies:

- name
- subject
- content
- template selection
- audience configuration

It does not copy:

- recipients
- counters
- tracking data
- send timestamps
- activity history

The duplicate always starts as `draft`.

## Preview and Test Email

The campaign editor must provide:

- Preview Email
- Send Test Email

Test sends:

- may target one or more administrator-supplied addresses
- must render the actual campaign design
- must support personalization sample data
- must not create campaign recipients
- must not affect analytics

## Personalization

Only allow explicit supported placeholders; do not execute arbitrary code from templates.

Missing subscriber values must have safe fallbacks.

Example:

`Habari {{first_name}}`

can gracefully fall back to a configured generic greeting if no first name exists.

## Click Tracking

Campaign links are rewritten to a signed/tracked route.

Flow:

1. Recipient clicks tracked link.
2. Application validates recipient/campaign identity.
3. Click event is recorded.
4. Recipient click summary fields are updated.
5. User is immediately redirected to the original URL.

Invalid tracking identifiers must fail safely and must not expose private subscriber information.

## Analytics UI

Primary campaign metrics:

- Total recipients
- Sent
- Pending
- Failed
- Suppressed
- Opened
- No tracked open
- Clicked
- Unsubscribed

Rates:

- Send success rate
- Open rate
- Click rate
- Click-to-open rate
- Unsubscribe rate

Recipient filters/tabs:

- All
- Sent
- Opened
- No tracked open
- Clicked
- Failed
- Suppressed
- Unsubscribed

CSV export should be available for campaign recipient/report data.

## Audience Health Dashboard

Add summary metrics for:

- Active subscribers
- New subscribers in the last 30 days
- Unsubscribed subscribers
- Suppressed addresses
- Never opened
- Inactive 90+ days

## Email Address Hygiene

Subscriber import and campaign preparation should flag suspicious domains such as common misspellings.

Example:

`user@gmal.com` -> `Possible typo: did you mean gmail.com?`

Never silently correct an address.

Administrator options:

- Correct
- Keep as entered
- Suppress

## Filament UX

Campaign creation/editing should be organized into these sections or steps:

1. Campaign Details
2. Content & Template
3. Audience
4. Preview & Test
5. Schedule & Send

Campaign detail page should show:

- status
- audience
- scheduled time
- started time
- completed time
- recipient statistics
- engagement statistics
- activity timeline

Contextual actions:

### Draft

- Preview
- Send Test
- Schedule
- Send Now
- Duplicate

### Scheduled

- Preview
- Cancel Schedule
- Duplicate

### Sending

- Pause
- Cancel

### Paused

- Resume
- Cancel

### Completed

- Resend to Non-openers
- Retry Failed
- Duplicate
- Export

### Failed

- Retry Failed
- Duplicate

## Queue and Batch Behavior

Continue using the database queue and existing configurable throttling.

Default production behavior:

- 20 recipients per batch
- 10 minutes between batches

Configuration:

- `MAIL_CAMPAIGN_BATCH_SIZE=20`
- `MAIL_CAMPAIGN_BATCH_DELAY_MINUTES=10`

Batch flow:

1. Campaign queued.
2. Load next pending batch.
3. Re-check subscriber eligibility and suppression.
4. Send recipients individually.
5. Record sent/failed/suppressed state.
6. Record audit event.
7. If pending recipients remain, schedule next batch after configured delay.
8. Otherwise complete campaign.

Pause/cancel state must be checked before every batch.

Prevent duplicate concurrent execution for the same campaign with a campaign-level lock or queue uniqueness mechanism.

## cPanel SMTP Limitation

While using cPanel SMTP, the application can reliably track:

- queued
- sent to SMTP
- failed during SMTP send
- suppressed
- opened
- clicked
- unsubscribed

It must not claim confirmed mailbox delivery.

Future provider integrations may add:

- delivered
- bounced
- complained

The service architecture should allow those provider events to be added without redesigning the campaign subsystem.

## Backward Compatibility

Existing recipient states `pending`, `sent`, and `failed` remain valid.

Existing open tracking fields and tracking tokens remain valid.

Existing audience scopes `subscribed`, `selected`, and `group` remain valid.

Historical campaigns with status `sent` remain readable and should be interpreted as completed history.

Migrations should prefer additive nullable/defaulted fields and new tables rather than destructive rewrites.

Existing subscriber unsubscribe tokens must remain valid.

## Testing Strategy

Tests must cover at least:

### Audience

- all active subscribers
- selected subscribers
- group subscribers
- new subscribers
- recent subscribers
- opened selected campaign
- no tracked open
- never opened
- inactive
- clicked/not clicked
- suppressed excluded
- unsubscribed excluded

### Suppression

- manual suppression
- unsubscribe suppression
- invalid address
- repeated failure
- re-subscribe behavior

### Sending

- batch size
- delayed next batch
- pause
- resume
- cancel
- no duplicate batch processing
- suppression checked immediately before send
- retry failed

### Tracking

- first open
- repeated open
- first click
- repeated click
- redirect destination
- invalid tracking token

### Personalization

- supported placeholders
- missing first-name fallback
- devotion placeholders
- unsubscribe URL

### Campaign actions

- duplicate
- resend to non-openers
- retry failed
- test email
- schedule
- cancel schedule

### Analytics

- sent
- failed
- suppressed
- opened
- no tracked open
- clicked
- unsubscribed
- rate calculations

### Authorization

- admin-only management actions
- public unsubscribe route
- public tracking routes

Use database transactions where the existing test schema is already available to avoid unnecessarily rebuilding the full MySQL test schema for every test class.

## Rollout

Development happens on:

`feature/advanced-email-campaigns`

Flow:

1. Implement and test on feature branch.
2. Merge into staging only after the relevant and full test suites pass.
3. Use a controlled staging subscriber group of about 25 recipients.
4. Verify 20-recipient first batch and delayed remainder.
5. Verify pause/resume/cancel.
6. Verify unsubscribe/suppression.
7. Verify open and click tracking.
8. Verify resend to non-openers and retry failed.
9. Promote to main only after staging validation.

Operational enablement order:

1. Unsubscribe + suppression
2. Audience segmentation
3. Pause/resume/cancel
4. Retry failed
5. Preview/test send
6. Personalization
7. Click tracking
8. Advanced analytics
9. Templates
10. Resend to non-openers

## Success Criteria

The implementation is successful when:

- existing campaigns and subscribers continue to work
- unsubscribed/suppressed addresses cannot accidentally receive campaign mail
- campaigns can be paused, resumed, and cancelled safely
- failed recipients can be retried without changing original history
- non-openers can be used to create a new resend campaign
- new/recent/inactive/engagement-based subscriber segments work correctly
- test sends and previews do not contaminate analytics
- click tracking works without exposing subscriber data
- campaign analytics distinguish SMTP `sent` from true provider delivery
- batching continues to honor configured rate limits
- all new tests pass and the full application test suite remains green
