# Changelog

All notable changes to Simple Post Like will be documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.1] — 2026-10-23

### Added
- `add_theme_support( 'simple-post-like' )` turns off automatic insertion for themes that print the button themselves
- `simple_post_like_auto_inject` filter to control automatic insertion per context and post
- `simple_post_like_guest_ip` filter for sites behind a proxy or CDN
- Plugin data is removed when the plugin is deleted (`uninstall.php`)

### Changed
- **Breaking:** template function renamed from `spl_like_button()` to `simple_post_like_button()`
- **Breaking:** hooks renamed to the `simple_post_like_` prefix: `count_format`, `allowed_post_types`, `guest_likes_enabled`, `before_like`, `after_like`, `after_unlike`
- Inline SVG heart replaces Font Awesome, so no external requests
- Frontend script loads only on pages that show a button
- Hover colors follow the `--spl-color` custom property
- The settings page no longer hides admin notices from WordPress and other plugins

### Fixed
- Like buttons added after page load (load more, infinite scroll) now respond to clicks
- The stored like count could drift from the actual likes
- Draft and private posts could be liked by post ID
- The shortcode now uses the saved Display Style when no style is given
- An unknown shortcode `style` falls back to the saved setting
- The like label stayed plural ("1 Likes") until the page was reloaded
- Sorting the Posts list by Likes no longer hides posts without likes
- Counts near one million showed as "1000K"
- The "Settings saved" notice appeared for other admins
- A missing vendor folder shows an admin notice instead of a fatal error
- Escaping and input sanitization flagged by Plugin Check
- Admin notices from WordPress and other plugins appeared inside the settings page header

### Removed
- Font Awesome CDN dependency

## [1.0.0] — 2026-03-13

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

[1.0.1]: https://github.com/fronttheme/simple-post-like/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/fronttheme/simple-post-like/releases/tag/v1.0.0