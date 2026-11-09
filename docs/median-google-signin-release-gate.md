# Median Google Sign-In: Android release gate

Status: **blocking verification — 2026-11-09**. Target: Google Play; do not merge this finding away or declare Google Sign-In working without a device test.

## Why this is a release gate

The Laravel website currently uses `/auth/google` and `/auth/google/callback` through Laravel Socialite. The current Google OAuth provider uses `stateless()`, which bypasses the ordinary session-state verification. It has not been demonstrated that the website Google OAuth flow is secure or functional in the Median Android WebView.

Google disallows OAuth sign-in through embedded WebViews. Median's own guidance recommends its **Social Login plugin with native SDKs**, not simply opening Google OAuth in the WebView. Browser and WebView do not share sessions.

References:
- https://docs.median.co/docs/authentication
- https://docs.median.co/docs/social-login
- https://developers.google.com/identity/siwg/best-practices
- https://laravel.com/docs/12.x/socialite

## Required integration work

1. In Median App Studio inspect whether **Social Login** is available/enabled for the Uzima Milele app, and record plugin version/requirements and licensing. Do not assume it is included in the current plan.
2. Establish a final Android package name and signing certificate SHA-1; follow Median's Google setup to configure Google OAuth clients.
3. Use Median's documented JavaScript Bridge website integration to invoke native Google Sign-In **only in the Median app**. Preserve browser Google Sign-In on the regular website.
4. Implement a secure server-side identity/token verification exchange per Median's documented backend contract, with audience/issuer/expiry/nonce checks appropriate to the returned credential. **Do not trust a Google email or user ID sent by client-side JavaScript without cryptographic verification**.
5. Issue Laravel's own authenticated session only after verifying identity; regenerate session ID; review account linking to prevent takeover. If Median login opens a browser, confirm final session lands in WebView rather than only in Chrome.
6. On a disposable staging student account, test native login, registration/linking, logout, app restart, session persistence, failure/cancellation and account deletion.
7. Revisit website Socialite `stateless()`: prefer session-bound state validation for ordinary browser login after verifying hosting and OAuth redirects. Add tests for CSRF and redirect safety.

## What can be checked now on staging

- [ ] `APP_DEBUG=false` in publicly accessible staging.
- [ ] Confirm `SESSION_SECURE_COOKIE=true` and host-appropriate domain; these were previously observed on DUHosting.
- [ ] Test password login and account settings using a disposable staging student.
- [ ] Confirm Median configuration and the Android package name, SHA-1, social-login licensing and how its callback is delivered.
- [ ] Do not input live OAuth credentials or private keys into GitHub or screenshots.

## Release decision

Google Sign-In is **unverified** for Median. This file is a gate/checklist, not a claim that the native integration has been implemented. Do not release the app until a native Google sign-in test passes on a signed Android build.
