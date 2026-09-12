# Devotion Email Auto-Scheduling Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Automatically create and schedule devotion email campaigns using a configurable default send time and audience, while allowing a per-devotion time override.

**Architecture:** Store global automation configuration in a dedicated `email_settings` table. Store the optional send-time override on each devotion. A focused devotion email automation service will synchronize one campaign per devotion and reuse the existing `EmailCampaignService` for recipient snapshotting and scheduling.

**Tech Stack:** Laravel 12, PHP 8.3, Filament 3, Eloquent, MySQL, PHPUnit.

**Spec:** `docs/superpowers/specs/2026-09-13-devotion-email-auto-scheduling-design.md`

## Global Constraints

- Preserve the existing devotion fields and public devotion behavior.
- Reuse the existing email campaign scheduling infrastructure.
- One automatic email campaign maximum per devotion.
- Sent campaigns must never be automatically resent.
- Past devotion send times must not auto-send.
- Global default audience supports all subscribers or one saved subscriber group.
- Per-devotion send time may override the global default.

---

### Task 1: Email automation settings

**Files:**
- Create migration for `email_settings`
- Create `app/Models/EmailSetting.php`
- Test: `tests/Feature/EmailSettingTest.php`

**Produces:**
- `EmailSetting::current(): EmailSetting`
- `auto_schedule_devotions`
- `default_devotion_send_time`
- `default_recipient_scope`
- `email_subscriber_group_id`

- [ ] Write failing settings test.
- [ ] Run it and verify RED.
- [ ] Create migration and model.
- [ ] Run migration and test.
- [ ] Commit.

### Task 2: Per-devotion send-time override

**Files:**
- Create migration adding `email_send_time` to `devotions`
- Modify `app/Models/Devotion.php`
- Modify `app/Filament/Resources/DevotionResource.php`
- Test devotion effective send-time behavior.

**Produces:**
- `Devotion::effectiveEmailSendTime(): string`

- [ ] Write failing test.
- [ ] Verify RED.
- [ ] Add column/model behavior.
- [ ] Add Filament field with default from EmailSetting.
- [ ] Verify GREEN.
- [ ] Commit.

### Task 3: Automatic campaign synchronization

**Files:**
- Create `app/Services/DevotionEmailAutomationService.php`
- Modify devotion create/edit lifecycle hooks
- Test: `tests/Feature/DevotionEmailAutomationTest.php`

**Produces:**
- `sync(Devotion $devotion): ?EmailCampaign`

Behavior:
- Creates one devotion campaign.
- Uses devotion title for campaign name/subject.
- Applies configured recipient scope/group.
- Schedules future date/time.
- Leaves campaign draft for past date/time.
- Never duplicates a campaign.

- [ ] Write failing creation test.
- [ ] Verify RED.
- [ ] Implement minimum synchronization.
- [ ] Verify GREEN.
- [ ] Add update/no-duplicate tests.
- [ ] Add sent-campaign protection test.
- [ ] Commit.

### Task 4: Email Settings Filament UI

**Files:**
- Create Filament Email Settings page/resource.
- Test resource/page registration.

Fields:
- Auto Schedule Devotion Emails
- Default Devotion Send Time
- Default Audience
- Default Subscriber Group when group audience is selected

- [ ] Write failing Filament registration test.
- [ ] Verify RED.
- [ ] Implement settings UI.
- [ ] Verify GREEN.
- [ ] Commit.

### Task 5: Full integration verification

- [ ] Run devotion automation tests.
- [ ] Run email campaign audience tests.
- [ ] Run scheduling tests.
- [ ] Run subscriber group tests.
- [ ] Run full test suite.
- [ ] Manually create a future devotion and confirm scheduled campaign.
- [ ] Manually edit it and confirm no duplicate campaign.
- [ ] Commit final integration changes.
