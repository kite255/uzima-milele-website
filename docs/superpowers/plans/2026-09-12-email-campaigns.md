# Email Campaigns Implementation Plan

**Goal:** Build email campaigns for Uzima Milele with devotion/custom content, recipient snapshots, test emails, queued sending, and delivery tracking.

**Architecture:** Campaigns are stored in `email_campaigns`. Active subscribers are copied into `email_campaign_recipients` before sending. Laravel queue jobs send recipient emails and update campaign delivery counters.

**Tech Stack:** Laravel 12, PHP 8.3, Filament 3, Laravel Mail, Laravel Queue.

**Spec:** `docs/superpowers/specs/2026-09-12-email-campaigns-design.md`

## Tasks

1. Create campaign and recipient database schema.
2. Create `EmailCampaign` and `EmailCampaignRecipient` models.
3. Add model relationships and status helpers.
4. Create campaign mailables/templates.
5. Create recipient snapshot/send service.
6. Create queue job for campaign delivery.
7. Create Filament EmailCampaignResource.
8. Add preview and test-email actions.
9. Add send-now action.
10. Add campaign/recipient tests.
