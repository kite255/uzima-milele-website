# Devotion Email Auto-Scheduling Design

## Goal

Automatically create and schedule an email campaign whenever an administrator creates a devotion.

## Configuration

A persistent email settings record will control:

- Whether automatic devotion email scheduling is enabled.
- Default devotion email send time.
- Default recipient audience.
- Optional default subscriber group.

The settings will be manageable through Filament.

## Devotion Scheduling

Each devotion continues using `published_at` as its devotion/publication date.

A devotion will also have an optional `email_send_time`.

When creating a devotion:

1. The send-time field is prefilled from the global default.
2. The administrator may override the time for that devotion.
3. After the devotion is created, the system creates one associated devotion email campaign.
4. The campaign uses the configured default audience.
5. `scheduled_at` is built from the devotion's `published_at` date plus its effective send time.
6. The campaign is scheduled using the existing `EmailCampaignService`.

## Duplicate Protection

A devotion must never receive multiple automatic campaigns.

If the devotion already has an automatic campaign:

- Editing a draft or scheduled devotion updates that campaign.
- Editing a sent campaign does not create another campaign and does not resend it.

## Past Dates

If the resulting devotion email date/time is already in the past:

- The devotion is still saved.
- Its automatic campaign remains a draft.
- The system does not automatically send an old devotion.

## Audience

Supported defaults:

- All active subscribers.
- A saved subscriber group.

Selected individual subscribers will not be used as the global automatic devotion audience because that audience is not reusable.

## Deletion / History

Deleting or editing a devotion must not destroy historical sent campaign delivery records.

## Existing Infrastructure

The implementation will reuse:

- `EmailCampaign`
- `EmailCampaignService`
- `SendEmailCampaign`
- Existing campaign recipient snapshots
- Existing Laravel scheduler
- Existing subscriber groups
