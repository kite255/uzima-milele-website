# Uzima Milele Email Campaigns Design

## Goal

Add an email campaign system to Uzima Milele so administrators can send existing devotions or custom email messages directly to subscribed users without Mailchimp.

## Phase 1 Scope

The first version supports:

- Create email campaign
- Campaign type:
  - Devotion
  - Custom message
- Choose an existing devotion
- Custom email subject
- Custom rich-text content
- Choose recipient scope
- Send only to active subscribers
- Preview campaign
- Send test email
- Send campaign now
- Queue campaign delivery
- Snapshot campaign recipients
- Track sent and failed recipients
- Campaign status
- Campaign summary counts

Scheduling, open tracking, click tracking, advanced segmentation, and retry dashboards are excluded from Phase 1.

## Navigation

Filament navigation:

Barua Pepe
- Wasajili wa Barua Pepe
- Kampeni za Barua Pepe

## Campaign Statuses

Campaigns use:

- draft
- queued
- sending
- sent
- failed

## Campaign Types

### Devotion

The administrator selects an existing `Devotion`.

The campaign reuses the Uzima Milele devotion email design.

### Custom

The administrator provides:

- Campaign name
- Subject
- Rich-text message

## Recipient Scope

Phase 1 supports:

- All active subscribers

Only records where:

`status = subscribed`

may receive campaigns.

Recipients are copied into the campaign recipient table before sending.

This protects campaign history if subscriber data changes later.

## Database

### email_campaigns

Fields:

- id
- name
- type
- devotion_id nullable
- subject
- content nullable
- recipient_scope
- status
- total_recipients
- sent_count
- failed_count
- queued_at nullable
- sent_at nullable
- created_by nullable
- timestamps

### email_campaign_recipients

Fields:

- id
- email_campaign_id
- email_subscriber_id nullable
- name
- email
- status
- sent_at nullable
- failed_at nullable
- error_message nullable
- timestamps

Recipient statuses:

- pending
- sent
- failed

## Models

### EmailCampaign

Relationships:

- devotion()
- creator()
- recipients()

### EmailCampaignRecipient

Relationships:

- campaign()
- subscriber()

## Filament

Create:

`EmailCampaignResource`

Pages:

- ListEmailCampaigns
- CreateEmailCampaign
- EditEmailCampaign

The campaign list displays:

- Campaign name
- Type
- Subject
- Status
- Recipients
- Sent
- Failed
- Created date

## Campaign Form

### Campaign Details

Fields:

- name
- type

When type is `devotion`:

- devotion_id

When type is `custom`:

- subject
- content

### Recipients

Phase 1 recipient selection:

- All subscribed users

Display subscriber count before sending where practical.

## Preview

Administrators should be able to preview the rendered campaign before sending.

Devotion campaigns use:

`resources/views/emails/devotions/daily.blade.php`

Custom campaigns use a dedicated template:

`resources/views/emails/campaigns/custom.blade.php`

## Test Email

Before sending a campaign, the administrator can enter one email address and send a test message.

A test email:

- does not create campaign recipients
- does not change campaign status
- does not increase sent counts

## Campaign Sending

Sending must not occur directly inside the Filament HTTP request.

Flow:

1. Validate campaign.
2. Confirm campaign is still draft.
3. Read active subscribers.
4. Create recipient snapshot records.
5. Set campaign to queued.
6. Dispatch campaign job.
7. Worker changes status to sending.
8. Emails are processed in batches.
9. Each recipient is marked sent or failed.
10. Campaign counters are updated.
11. Campaign becomes sent when processing finishes.
12. Campaign becomes failed only when campaign-level processing cannot proceed.

## Queue

Use Laravel queue.

The main job:

`SendEmailCampaign`

The job processes recipient records in manageable batches rather than loading the whole subscriber list into memory.

## Safety

A campaign that is already:

- queued
- sending
- sent

must not be sent again using the normal Send action.

Unsubscribed users are excluded when the recipient snapshot is generated.

The recipient snapshot preserves the exact destination email used for the campaign.

## Email Personalization

Where subscriber information exists, emails may use the recipient's first name.

Devotion campaigns continue using the existing personalized devotion email structure.

## Failures

Individual mail failures:

- mark recipient `failed`
- record an error message
- increase `failed_count`
- continue processing other recipients

One failed recipient must not stop the entire campaign.

## Testing

Tests should cover:

- campaign creation
- devotion campaigns
- custom campaigns
- active subscribers selected
- unsubscribed users excluded
- recipient snapshots
- duplicate send prevention
- test emails do not affect campaign counts
- successful recipients marked sent
- failed recipients marked failed
- final campaign counters
- Filament admin access

## Future Phase

Later additions may include:

- scheduled campaigns
- subscriber segmentation
- open tracking
- click tracking
- bounce tracking
- retry failed recipients
- campaign duplication
- analytics dashboard
