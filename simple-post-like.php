<?php
/**
 * Plugin Name: Simple Post Like
 * Description: Add simple, intuitive reactions to your posts.
 * Plugin URI: https://www.fronttheme.com/products/simple-post-like
 * Version: 1.0.1
 * Author: FrontTheme
 * Author URI: https://www.fronttheme.com
 * Text Domain: simple-post-like
 * Domain Path: /languages/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Copyright: FrontTheme
 * Requires at least: 6.8
 * Requires PHP: 8.0
 */

use FrontTheme\SimplePostLike\Install;
use FrontTheme\SimplePostLike\Plugin;

defined('ABSPATH') || exit;

define('SIMPLE_POST_LIKE_FILE', __FILE__);
define('SIMPLE_POST_LIKE_VERSION', '1.0.1');
define('SIMPLE_POST_LIKE_PATH', plugin_dir_path(__FILE__));
define('SIMPLE_POST_LIKE_URL', plugin_dir_url(__FILE__));

// Stop loading and show a notice when the Composer autoloader is missing.
if (!file_exists(SIMPLE_POST_LIKE_PATH . 'vendor/autoload.php')) {
  add_action('admin_notices', function () {
    echo '<div class="notice notice-error"><p>';
    echo esc_html__('Simple Post Like could not load its files. Please install the plugin from the release zip, or run composer install.', 'simple-post-like');
    echo '</p></div>';
  });

  return;
}

require_once SIMPLE_POST_LIKE_PATH . 'vendor/autoload.php';
require_once SIMPLE_POST_LIKE_PATH . 'includes/functions.php';

// Initialize the plugin.
Plugin::instance();

register_activation_hook(__FILE__, [Install::instance(), 'activate']);
register_deactivation_hook(__FILE__, [Install::instance(), 'deactivate']);