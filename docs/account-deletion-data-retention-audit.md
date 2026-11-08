# Uzima Milele: account deletion and personal-data retention audit

Status: **implementation review / pending policy approval** (2026-11-08)

This review is based on the Laravel schema and deletion controller on `staging`. It is not a legal compliance certification or a verification of production data.

## Account deletion implemented

- Authenticated users must confirm their current password.
- The deletion controller now removes the email-address password reset token and deletes the user inside one database transaction. Logout and session invalidation follow successful completion.
- Dependent student records use foreign-key behaviors, which are tested separately against an isolated MySQL test database. **Do not assume every related record is erased.**
- A user with only a social login may need to establish a password through the documented reset flow before using self-service deletion.

## Independently retained personal data requiring policy decisions

| Table | Personal information | Relationship and risk | Decision required |
|---|---|---|---|
| `email_subscribers` | Name, email, phone and subscription preferences | Independent of `users`. Account deletion does not unsubscribe. | Specify how users request deletion of subscriber data and what suppression records are retained. |
| `email_campaign_recipients` | Snapshot name and email, delivery/open/click history | `email_subscriber_id` uses SET NULL; snapshots remain after subscriber removal. | Define retention period, permissible audit history, anonymization/erasure workflow. |
| `email_suppressions` | Email, reason, notes | Independent email-address record; may prevent unwanted future sends. | Decide the least-identifying suppression data needed and retention period. |
| `prayer_requests` | Name, optional contact, message | No user foreign key. Sensitive free-form content may remain after account deletion. | Establish verified request procedure and retention/deletion policy. |
| `testimonials` | Name, optional image and message | No user foreign key. May be published. | Establish withdrawal/unpublish and erasure procedures. |
| `quiz_results` | Historical result data | Existing schema expects `user_id` to SET NULL in the tested MySQL schema. | Check for other identifying columns and retention justification. |
| `exports`, `jobs`, `failed_jobs`, `notifications` | Potential copies of user data / payloads | Schema alone does not establish retention or deletion. | Audit payloads, logs, filesystem exports, backups, and cleanup schedules. |

## Before approving a final Privacy Policy or app-store disclosure

1. Verify actual data collected in the website and mobile app, including analytics, authentication providers, mailing providers, and backup copies.
2. Confirm contact addresses and the operational process for identifying and handling deletion requests.
3. Decide and document retention periods by category, including legally necessary and operationally justified exceptions.
4. Implement and test deletion/anonymization for independently collected records **only after** the policy has been approved.
5. Test password-based and Google-linked deletion flows, verify all affected foreign keys, and test that transaction failure does not leave partial deletions.
6. Review deletion behavior for instructor and administrator accounts separately, including reassignment of owned lesson content.
7. Test on a dedicated local database. Never run destructive test helpers on production or an existing student database.

## Commands (isolated environments only)

SQLite controller-level test:

```bash
DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test --filter=AccountDeletionTest
```

Dedicated local MySQL integration test (must be migrated separately):

```bash
APP_ENV=testing DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_DATABASE=uzima_milele_test UZIMA_ALLOW_MYSQL_TESTS=1 php artisan test --filter=AccountDeletionMySqlTest
```

Do not merge to `main` until the tests and staging checks are completed.
