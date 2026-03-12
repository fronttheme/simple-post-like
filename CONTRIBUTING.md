# Contributing to Simple Post Like

Thank you for your interest in contributing! Here is everything you need to get started.

---

## Ways to Contribute

- 🐛 **Report bugs** — open a [GitHub Issue](https://github.com/fronttheme/simple-post-like/issues)
- 💡 **Suggest features** — open an issue with the `enhancement` label
- 🔧 **Submit a pull request** — fixes, improvements, or new features
- 🌍 **Translations** — submit a `.po` file for your language

---

## Development Setup

### Requirements

- PHP 8.0+
- Composer
- Node.js 18+ and npm
- WordPress 6.8+

### Getting started

```bash
git clone https://github.com/fronttheme/simple-post-like.git
cd simple-post-like

# Install PHP dependencies
composer install

# Install Node dependencies and compile SCSS
npm install
npm run dev
```

`npm run dev` watches for SCSS changes and recompiles automatically.

---

## Project Structure

```
simple-post-like/
├── assets/
│   ├── css/              # Compiled CSS (committed to git)
│   ├── js/               # Vanilla ES6 (no build step)
│   └── scss/             # SCSS source
│       ├── _variables.scss
│       ├── simple-post-like.scss
│       ├── admin.scss
│       ├── frontend/
│       └── admin/
├── includes/
│   ├── classes/          # PHP classes (PSR-4 autoloaded)
│   └── traits/
├── languages/            # Translation files
├── simple-post-like.php  # Plugin entry point
├── composer.json
└── package.json
```

---

## Code Standards

### PHP
- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- PHP 8.0+ syntax is welcome (`match`, named arguments, union types)
- All user-facing strings wrapped in `esc_html__()` or equivalent
- All output must be properly escaped

### JavaScript
- Plain ES6+ — no jQuery, no frameworks, no build step
- Follow the existing style in `assets/js/`

### SCSS
- BEM naming — `.block__element--modifier`
- New components go in their own partial under `assets/scss/`
- Run `npm run build` before committing — always commit the compiled CSS

---

## Pull Request Process

1. Fork the repo and create a branch from `main`:
   ```bash
   git checkout -b fix/your-fix-name
   git checkout -b feature/your-feature-name
   ```

2. Make changes following the standards above

3. Compile SCSS if you changed any `.scss` files:
   ```bash
   npm run build
   ```

4. Test on a clean WordPress 6.8+ install

5. Commit with a clear message:
   ```
   fix: prevent double like on rapid click
   feat: add reaction count to REST API response
   docs: update shortcode examples in README
   ```

6. Open a pull request against `main` describing what changed and why

---

## Reporting Bugs

Please include: WordPress version, PHP version, plugin version, steps to reproduce, expected vs actual behaviour, and any relevant errors.

---

## Questions

Open a [GitHub Discussion](https://github.com/fronttheme/simple-post-like/discussions) for general questions.