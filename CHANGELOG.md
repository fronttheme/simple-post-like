# Changelog

All notable changes to Simple Post Like will be documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).
This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.0] — 2025-03-12

### Added
- Initial public release
- Like / unlike toggle for any registered post type
- Three display styles: `button_default`, `icon_counter`, `icon_only`
- Auto-injection via `the_content` filter — before, after, both, or none
- Archive page injection — show likes on category, tag, and blog index pages
- Master toggle to disable auto-injection (shortcode-only mode)
- Guest like support tracked by hashed IP (SHA-256 + `NONCE_SALT`)
- Allow Guests setting to restrict likes to logged-in users only
- Statistics admin tab — total likes, liked posts count, most liked posts table
- Sortable "Likes" column in the WordPress Posts list table
- Shortcode `[simple_post_like]` with `post_id` and `style` attributes
- Settings page under **Settings → Simple Post Like**
- Font Awesome fallback — auto-enqueued if not already loaded by the theme
- PSR-4 autoloading via Composer
- Translation-ready with `.pot` file
- No jQuery dependency — pure vanilla ES6 JavaScript
- SCSS source with modular partials — compiled CSS included for non-npm users

[1.0.0]: https://github.com/fronttheme/simple-post-like/releases/tag/v1.0.0