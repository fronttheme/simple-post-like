=== Simple Post Like ===
Contributors: farukahmed
Tags: like, post like, reactions, like button, post reactions
Requires at least: 6.8
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add simple, intuitive reactions to your posts.

== Description ==

Simple Post Like is a lightweight, developer-friendly WordPress plugin that adds a clean like button to your posts. No bloat, no jQuery, no theme dependency — just a focused, well-built feature that works anywhere.

**Key Features**

* Three display styles — Button, Icon + Counter, Icon Only
* Auto-injection — before content, after content, both, or none
* Archive page injection — like buttons on blog index and category pages
* Guest likes — optional, tracked by hashed IP (GDPR-friendly)
* Shortcode support — `[simple_post_like]` for manual placement
* Statistics dashboard — total likes, most liked posts, sortable Posts column
* Works with any theme — no theme dependency whatsoever
* Pure Vanilla JS — no jQuery required
* Developer-friendly — clean hooks, filters, and shortcode attributes

**Display Styles**

* `button_default` — Icon + label + like count
* `icon_counter` — Icon with count beside it
* `icon_only` — Compact circle icon button

**Shortcode Usage**

Place the like button anywhere manually:

`[simple_post_like]`
`[simple_post_like post_id="123"]`
`[simple_post_like style="icon_only"]`

**For Developers**

The plugin is open source and actively maintained on GitHub:
https://github.com/fronttheme/simple-post-like

CSS custom properties are exposed on `:root` so you can restyle
the button entirely from your theme without touching plugin files:
```css
:root {
  --spl-color:      #your-brand-color;
  --spl-btn-radius: 4px;
}
```

== Installation ==

1. Upload the `simple-post-like` folder to `/wp-content/plugins/`
2. Activate the plugin through the **Plugins** menu in WordPress
3. Go to **Settings → Simple Post Like** to configure

Or install directly from the WordPress admin:

1. Go to **Plugins → Add New**
2. Search for **Simple Post Like**
3. Click **Install Now** then **Activate**

== Frequently Asked Questions ==

= Does it work with any theme? =

Yes. Simple Post Like has no theme dependency. It works with any WordPress theme.

= Can guests like posts without an account? =

Yes, if you enable **Allow Guest Likes** in Settings. Guest likes are tracked by a hashed IP address — raw IPs are never stored.

= How do I place the button manually? =

Use the shortcode `[simple_post_like]` anywhere in your content, or `[simple_post_like post_id="123" style="icon_only"]` for specific posts with a custom style.

= Can I disable auto-injection and use shortcodes only? =

Yes. Go to **Settings → Simple Post Like → Auto Injection** and disable the master toggle. The button will only appear where you place the shortcode.

= Does it require jQuery? =

No. The frontend script is pure Vanilla JavaScript. No jQuery dependency.

= Is it GDPR-friendly? =

Guest likes are tracked using a SHA-256 hashed IP combined with WordPress's NONCE_SALT — raw IP addresses are never stored in the database.

= Can I restyle the button to match my theme? =

Yes. The button exposes CSS custom properties on `:root`. Override `--spl-color`, `--spl-btn-radius`, and others in your theme's stylesheet.

= Where can I report a bug or request a feature? =

Please open an issue on GitHub:
https://github.com/fronttheme/simple-post-like/issues

== Screenshots ==

1. The like button in default button style
2. Icon + counter and icon-only display styles
3. Settings page — Button and Auto Injection sections
4. Statistics tab — total likes and most liked posts

== Changelog ==

= 1.0.0 =
* Initial public release
* Three display styles: button, icon + counter, icon only
* Auto-injection with before/after/both/none placement options
* Archive page injection toggle
* Guest likes with hashed IP tracking
* Shortcode with post_id and style attributes
* Statistics dashboard with most liked posts table
* Sortable Likes column in Posts list table
* Pure Vanilla JS — no jQuery
* PHP 8.0+ with PSR-4 autoloading via Composer

== Upgrade Notice ==

= 1.0.0 =
Initial release. No upgrade steps required.