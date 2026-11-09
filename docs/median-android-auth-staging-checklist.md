# Median Android authentication: staging verification

Status: **pending device and hosted staging verification**.

## Verified from Laravel source
- Student registration, email/password login, Google OAuth redirect/callback, password reset and logout routes exist.
- Password login and Google login regenerate the session. Registration now regenerates the session as well.
- Default session driver is configured through `SESSION_DRIVER` (default: database), with `SESSION_DOMAIN`, `SESSION_SECURE_COOKIE`, and `SESSION_SAME_SITE` environment overrides.
- Google Socialite currently uses `stateless()`. The OAuth callback relies on Google user info, without Laravel's session-bound OAuth state verification. This warrants a security review; do not assume this is safe just because browser login succeeds.
- The app uses Median according to the project owner. Median external browser and cookie behavior cannot be verified from this Laravel repository.

## Required staging checks (disposable account only)
- [ ] Confirm Step 2 profile/delete-account changes were actually deployed to `new.uzimamilele.or.tz`.
- [ ] Use real Android Median build: register, log in, close/reopen app, refresh page, and verify session persists.
- [ ] Sign out and verify protected routes require authentication.
- [ ] Complete password reset end-to-end with a staging test inbox.
- [ ] Google login: verify account selection, return to the app, correct redirect, and preserved session.
- [ ] Confirm Google authentication is resistant to login CSRF/account-confusion; revisit `stateless()` and redirect handling before release.
- [ ] Validate HTTPS and exact staging cookie domain, secure and SameSite settings in the deployed environment; never hard-code production cookie settings into local development.
- [ ] Verify account deletion for a disposable student, and record data-retention caveats.
- [ ] Verify real error logs contain no OAuth codes, tokens or user passwords.
- [ ] Check external browser OAuth handoff and deep-link return in Median settings.

## Staging diagnostics (non-destructive)

```bash
php artisan about --only=environment
php artisan route:list --path=login
php artisan route:list --path=register
php artisan route:list --path=auth/google
php artisan tinker --execute='dump([
    "session_driver" => config("session.driver"),
    "session_domain" => config("session.domain"),
    "session_secure" => config("session.secure"),
    "session_same_site" => config("session.same_site"),
]);'
```

Do not copy `.env` secrets into chat. Avoid posting account tokens or Google OAuth callback URLs containing codes.
