# Uzima Milele Watoto — Child safety release verification

These changes improve the existing *public*, no-registration children’s learning experience. They do **not** establish Google Play Families compliance by themselves.

## Implemented on this feature branch

- Quiz results show the correct count and percentage (the prior view divided an already-percent score again).
- Quiz submissions validate answer values and must match the question IDs shown in the current visitor's session.
- New public Watoto quiz result records use the neutral label `Guest` instead of saving an authenticated person's name. **Existing named records are not automatically erased**.
- YouTube URLs are restricted to recognized HTTPS YouTube hosts and exact 11-character video IDs; rendered embeds use `youtube-nocookie.com`.
- Video iframes are lazy-loaded and have a constrained referrer policy; malformed links do not become iframe sources.
- Quiz radio inputs remain keyboard accessible.

## Still required before Google Play publication

- Confirm what ages the ministry targets and declare them accurately in Google Play Console.
- Inspect **all** Median SDKs, app permissions, analytics, ads, chatbot embeds, and the wider site's third-party scripts as actually loaded for children.
- Google Play Families policies apply when children are included in the target audience; YouTube privacy-enhanced mode is **not** a certification of compliance, nor proof of zero tracking.
- Review every published video for child suitability, embedded player behavior, recommendations, ads and links out to other apps (including WhatsApp sharing).
- Determine and document lawful basis, retention period and deletion approach for `watoto_quiz_results` (including existing named records), access logs, backups, and any other personal data.
- Finalize a Kiswahili Privacy Policy with an accurate child-specific section, a guardian contact/deletion request process, and disclosures for embedded media.
- Verify all pages on signed Median Android builds and real devices; test session-driven quiz submission and browser/back-navigation behavior.
- Review the site-wide registration and OAuth flow where children may use accounts; do not claim parental consent flows exist when they do not.

## Safe test command

```bash
DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test --filter=WatotoSafetyTest
```

The isolated SQLite schema intentionally avoids running MySQL migrations against a live database.

## Release status

**Pending verification.** Keep this change in staging until tests, device review and the policy/SDK audit are complete.
