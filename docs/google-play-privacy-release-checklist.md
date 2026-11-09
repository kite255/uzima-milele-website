# Uzima Milele — Google Play privacy release checklist

Status: **staging review draft — not approved for Play Console publication** (2026-11-09).

## Verified from the `staging` code

- Public URLs: `/privacy-policy` and `/account-deletion` (Laravel route views).
- Google login requests `openid`, `profile`, `email`, and persists Google ID, email and profile image where provided.
- Users have names, email and optional phone; lessons store enrolment, progress, quiz and certificate information.
- Email subscribers are stored separately from users, and campaigns retain address/name snapshots and tracking events.
- Prayer requests and testimonials collect names and free-form information independently of login accounts.
- Shared layout loads Elfsight chatbot, Google Fonts, CDN-hosted Tailwind and Alpine.
- Public account deletion requires authenticated password confirmation; social-login-only users may need password reset. Password-reset token is deleted with the user.
- The frontend is wrapped using Median according to the app owner; Median native plugin settings are not visible in this Laravel repository.
- Existing privacy policy is a **draft**, not yet approved.

## Required confirmations before changing the draft into a final policy

- [ ] Confirm exact developer/publisher name used in the Play listing and the reachable privacy contact address.
- [ ] Review Median App Studio plugin list, Android permissions, device data identifiers, analytics, crash reporting, push notifications and third-party SDKs.
- [ ] Check active website analytics, tracking tags and cookie/session behavior in production, not just source code.
- [ ] Confirm every processor/provider that receives data (hosting, email/SMPP provider if any, Google, Median, chatbot, analytics).
- [ ] Document retention period or event-based retention criterion for accounts, lesson records, campaign snapshots, unsubscribed addresses, prayer requests, testimonials, log files and backups.
- [ ] Implement request handling, erasure and any permitted retained-data exceptions; verify related data is handled after account deletion.
- [ ] Confirm secure transfer, access restrictions and incident/contact process.
- [ ] Verify the policy URL is publicly available without login and linked from within the Android app.
- [ ] Align every Data Safety declaration with observed behavior and the final privacy policy.
- [ ] Obtain ministry approval and perform a dedicated staging test before promoting to `main`.

## Release discipline

Changes should be tested on `staging` first. Do not publish the policy as a finalized compliance claim until missing details are confirmed. Use the production public HTTPS URL rather than the staging domain in Play Console.
