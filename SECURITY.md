# Security Policy

## Supported Versions

Only the latest release of Simple Post Like receives security fixes.

| Version | Supported |
|---|---|
| 1.0.x (latest) | ✅ |
| < 1.0.0 | ❌ |

---

## Reporting a Vulnerability

**Please do not report security vulnerabilities via public GitHub Issues.**

If you discover a security vulnerability, please report it responsibly by emailing:

**farukahmedinbox@gmail.com**

Include as much detail as possible:
- A description of the vulnerability
- Steps to reproduce
- Potential impact
- Any suggested fix

You will receive a response within **48 hours** acknowledging your report. We aim to release a fix within **14 days** for confirmed critical vulnerabilities.

We ask that you:
- Give us reasonable time to fix the issue before public disclosure
- Not exploit the vulnerability or access user data beyond what is needed to demonstrate the issue

---

## Security Measures in This Plugin

- All AJAX requests are nonce-verified via `check_ajax_referer()`
- All output is escaped using WordPress escaping functions (`esc_html`, `esc_attr`, `esc_url`)
- Guest like tracking uses SHA-256 hashed IPs combined with `NONCE_SALT` — raw IPs are never stored
- Settings form is protected by `check_admin_referer()` and `current_user_can('manage_options')`
- Post IDs are cast with `absint()` before any database interaction
- No raw SQL queries — all data is stored via WordPress post meta APIs