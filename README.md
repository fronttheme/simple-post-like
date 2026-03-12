<div align="center">
  <img src="https://www.fronttheme.com/wp-content/uploads/2026/03/simple-post-like-cover.webp" alt="Simple Post Like - Cover" />
  <h1>Simple Post Like</h1>
  <p>Add simple, intuitive reactions to your posts.</p>

![Version](https://img.shields.io/badge/version-1.0.0-e03e52?style=flat-square)
![WordPress](https://img.shields.io/badge/WordPress-6.8%2B-3858e9?style=flat-square&logo=wordpress&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-7a86b8?style=flat-square&logo=php&logoColor=white)
![License](https://img.shields.io/badge/license-GPL--2.0--or--later-green?style=flat-square)
![Free](https://img.shields.io/badge/free-forever-e03e52?style=flat-square)

[Product Page](https://www.fronttheme.com/products/simple-post-like/) ·
[Documentation](https://www.fronttheme.com/docs/simple-post-like/) ·
[Report a Bug](https://github.com/fronttheme/simple-post-like/issues) ·
[Request a Feature](https://github.com/fronttheme/simple-post-like/issues)

<br>

[![Full Documentation](https://img.shields.io/badge/Full%20Documentation-%20fronttheme.com-6f42c1?style=for-the-badge&logo=wordpress&logoColor=white)](https://www.fronttheme.com/docs/simple-post-like/)

</div>

---

Simple Post Like is a lightweight, developer-friendly WordPress plugin that adds a clean like button to your posts. No bloat, no jQuery, no theme dependency — just a focused, well-built feature that works with any theme.

Three display styles. Flexible auto-injection. Guest likes with hashed IP tracking. A statistics dashboard. A sortable Likes column in your Posts list. All configurable from a clean admin page under **Settings → Simple Post Like**.

---

## Features

- **Three display styles** — Button with label, Icon + Counter, Icon Only
- **Auto-injection** — before content, after content, both, or none on single posts
- **Archive injection** — like buttons on blog index, category, tag, and search pages
- **Guest likes** — optional, tracked by SHA-256 hashed IP (GDPR-friendly, no raw IPs stored)
- **Shortcode** — `[simple_post_like]` for manual placement anywhere
- **Statistics dashboard** — total likes, posts with likes, most liked posts leaderboard
- **Posts list column** — sortable Likes column on `wp-admin/edit.php`
- **CSS custom properties** — restyle the button entirely from your theme, no plugin files needed
- **Pure Vanilla JS** — no jQuery dependency whatsoever
- **PHP 8.0+ throughout** — PSR-4 autoloading, type hints, singleton pattern
- **GPL-2.0-or-later** — free for personal and commercial use

---

## Requirements

| Requirement | Version |
|---|---|
| WordPress | 6.8 or higher |
| PHP | 8.0 or higher |

---

## Installation

### From GitHub (ZIP)

1. Go to [Releases](https://github.com/fronttheme/simple-post-like/releases)
2. Download `simple-post-like.zip` from the latest release
3. In WordPress admin go to **Plugins → Add New → Upload Plugin**
4. Upload the zip and click **Install Now**, then **Activate**

### Manual

1. Download and unzip the latest release
2. Upload the `simple-post-like` folder to `/wp-content/plugins/`
3. Activate via **Plugins → Installed Plugins**

### From WordPress.org *(coming soon)*
```
Plugins → Add New → Search "Simple Post Like" → Install → Activate
```

---

## Usage

Once activated, go to **Settings → Simple Post Like** to configure.

### Auto-Injection

The like button injects automatically into post content based on your settings. Choose placement — after content, before content, before and after, or disabled — and toggle archive page injection separately.

Disable the master **Auto Injection** toggle to switch to shortcode-only mode.

### Shortcode

Place the like button anywhere manually:
```
[simple_post_like]
[simple_post_like post_id="123"]
[simple_post_like style="icon_only"]
[simple_post_like style="icon_counter"]
```

**Shortcode attributes:**

| Attribute | Default | Options |
|---|---|---|
| `post_id` | current post | any valid post ID |
| `style` | setting value | `button_default`, `icon_counter`, `icon_only` |

### Theming

Simple Post Like exposes CSS custom properties on `:root`. Override them in your theme to restyle the button without touching plugin files:
```css
:root {
  --spl-color:        #your-brand-color;
  --spl-btn-radius:   4px;
  --spl-font-size:    14px;
  --spl-transition:   0.2s ease;
}
```

Full list of available properties in the [Documentation](https://www.fronttheme.com/docs/simple-post-like/).

---

## Project Structure
```
simple-post-like/
├── assets/
│   ├── css/                  # Compiled CSS (committed)
│   ├── js/
│   │   ├── admin.js          # Admin UI — radio card sync
│   │   └── simple-post-like.js  # Frontend — pure Vanilla JS
│   └── scss/
│       ├── _variables.scss   # Shared Sass vars + CSS custom property mixins
│       ├── admin.scss        # Admin entry point
│       ├── simple-post-like.scss  # Frontend entry point
│       ├── admin/            # Admin partials
│       └── frontend/         # Frontend partials
├── includes/
│   ├── classes/
│   │   ├── Admin.php         # Admin page (Settings + Statistics tabs)
│   │   ├── AjaxHandler.php   # Like/unlike AJAX handler
│   │   ├── Assets.php        # Script and style enqueue
│   │   ├── ContentHook.php   # Auto-injection via the_content filter
│   │   ├── Install.php       # Activation, deactivation, version checks
│   │   ├── LikeButton.php    # Button HTML renderer
│   │   ├── Plugin.php        # Core bootstrap
│   │   ├── Settings.php      # Plugin options (get, save, sanitize)
│   │   ├── Shortcode.php     # [simple_post_like] shortcode
│   │   └── Stats.php         # Statistics queries + Posts list column
│   └── traits/
│       └── Singleton.php     # Singleton trait
├── languages/                # i18n .pot file
├── vendor/                   # Composer autoloader (in release zip, not in git)
├── composer.json
├── package.json
└── simple-post-like.php      # Plugin entry point
```

---

## Development

### Requirements

- Node.js 18+
- npm
- Composer

### Setup
```bash
git clone https://github.com/fronttheme/simple-post-like.git
cd simple-post-like
npm install
composer install
```

### Scripts
```bash
npm run dev      # Watch and compile SCSS with source maps
npm run build    # Compile and compress SCSS for production
npm run pot      # Generate .pot translation file
npm run package  # Full release build → simple-post-like.zip
```

### SCSS Architecture

Source files live in `assets/scss/`. Compiled CSS is committed to git so users installing from GitHub don't need npm. The Sass build uses modern `@use`/`@forward` module syntax — no `@import`.
```
scss/
├── _variables.scss         # $spl-* Sass vars, CSS custom property mixins
├── admin.scss              # Entry: @use admin/* partials
├── simple-post-like.scss   # Entry: @use frontend/* partials
├── admin/
│   ├── _root.scss          # :root { --spl-* } for admin
│   ├── _layout.scss        # Wrap, header, tabs
│   ├── _sections.scss      # Section cards, form table
│   ├── _controls.scss      # Radios, checkboxes, toggles, shortcode ref
│   └── _statistics.scss    # Stat cards, table, rank badges
└── frontend/
    ├── _root.scss          # :root { --spl-* } for frontend
    ├── _button.scss        # Base button + liked state
    ├── _modes.scss         # button_default / icon_counter / icon_only
    └── _misc.scss          # @keyframes + .spl-auto-inject wrapper
```

---

## Built With

![PHP](https://img.shields.io/badge/PHP-8.0%2B-7a86b8?style=flat-square&logo=php&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6%2B-f7df1e?style=flat-square&logo=javascript&logoColor=black)
![Sass](https://img.shields.io/badge/Sass-SCSS-cc6699?style=flat-square&logo=sass&logoColor=white)
![Composer](https://img.shields.io/badge/Composer-PSR--4-885630?style=flat-square&logo=composer&logoColor=white)

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for the full release history.

---

## Contributing

Contributions are welcome! Please read [CONTRIBUTING.md](CONTRIBUTING.md) before submitting a pull request.

---

## Security

Please do not report security vulnerabilities through public GitHub issues. See [SECURITY.md](SECURITY.md) for the responsible disclosure process.

---

## License

Released under the [GPL-2.0-or-later](LICENSE) license — the same license as WordPress itself.
Free for personal and commercial use. No license keys. No upsells.

---

<div align="center">
  <sub>Built by <a href="https://www.fronttheme.com">FrontTheme</a> · <a href="https://www.farukdesign.com">Faruk Ahmed</a></sub>
</div>