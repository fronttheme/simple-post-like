<?php

namespace FrontTheme\SimplePostLike;

use Exception;
use FrontTheme\SimplePostLike\Traits\Singleton;

defined('ABSPATH') || exit;

/**
 * Manages plugin installation, activation, deactivation,
 * version updates, and compatibility checks.
 */
class Install {
  use Singleton;

  /**
   * Option name for storing the plugin version.
   */
  const VERSION_OPTION = 'simple_post_like_version';

  /**
   * Option name for storing the installation timestamp.
   */
  const INSTALLED_OPTION = 'simple_post_like_installed';

  /**
   * Transient name for skipping version checks.
   */
  const VERSION_CHECK_TRANSIENT = 'simple_post_like_version_checked';

  protected function init(): void {
    // Hook version checking early on init.
    add_action('init', [__CLASS__, 'check_version'], 5);

    // Hook compatibility checks on admin init.
    add_action('admin_init', [__CLASS__, 'check_compatibility']);
  }

  /**
   * Check the plugin version and run updates if necessary.
   */
  public static function check_version(): void {
    // Skip check if transient is set (performance optimization).
    if (get_transient(self::VERSION_CHECK_TRANSIENT)) {
      return;
    }

    $installed_version = get_option(self::VERSION_OPTION, '0.0.0');
    $current_version = SIMPLE_POST_LIKE_VERSION;

    if (version_compare($installed_version, $current_version, '<')) {
      self::run_update($installed_version, $current_version);
      update_option(self::VERSION_OPTION, $current_version);

      // Set transient to skip checks for 1 hour.
      set_transient(self::VERSION_CHECK_TRANSIENT, true, HOUR_IN_SECONDS);
    }
  }

  /**
   * Run the update process for the plugin.
   *
   * @param string $installed_version The currently installed version.
   * @param string $current_version The current plugin version.
   */
  protected static function run_update(string $installed_version, string $current_version): void {
    try {
      // Example update routines based on a version.
      if (version_compare($installed_version, '1.1.0', '<')) {
        // Add update logic for version 1.1.0 (e.g., database migrations, new options).
        self::update_to_1_1_0();
      }

      if (version_compare($installed_version, '1.2.0', '<')) {
        // Add update logic for version 1.2.0.
        self::update_to_1_2_0();
      }

      // Record installation time if not already set.
      self::record_installation();
    } catch (Exception $e) {
      // Log errors during updates.
      self::log_error('Update failed: ' . $e->getMessage());
    }
  }

  /**
   * Activation hook.
   */
  public function activate(): void {
    try {
      // Flush rewrite rules for custom post-types.
      flush_rewrite_rules();

      // Record installation time and initial version.
      self::record_installation();

      // Set the initial version if not exists.
      if (!get_option(self::VERSION_OPTION)) {
        update_option(self::VERSION_OPTION, SIMPLE_POST_LIKE_VERSION);
      }

      // Trigger an action for other components to hook into.
      do_action('simple_post_like_activated');
    } catch (Exception $e) {
      self::log_error('Activation failed: ' . $e->getMessage());
    }
  }

  /**
   * Deactivation hook.
   */
  public function deactivate(): void {
    try {
      // Flush rewrite rules on deactivation.
      flush_rewrite_rules();

      // Clear version check transient.
      delete_transient(self::VERSION_CHECK_TRANSIENT);

      // Trigger an action for other components to hook into.
      do_action('simple_post_like_deactivated');
    } catch (Exception $e) {
      self::log_error('Deactivation failed: ' . $e->getMessage());
    }
  }

  /**
   * Check compatibility with WordPress and PHP versions.
   */
  public static function check_compatibility(): void {
    global $wp_version;

    // Minimum required versions.
    $required_php_version = '7.4';
    $required_wp_version = '5.9';

    // Check PHP version.
    if (version_compare(PHP_VERSION, $required_php_version, '<')) {
      add_action('admin_notices', function () use ($required_php_version) {
        $message = sprintf(
        /* translators: %1$s: Required PHP version, %2$s: Current PHP version */
          esc_html__('SimplePostLike requires PHP version %1$s or higher. You are running version %2$s.', 'simple-post-like'),
          esc_html($required_php_version),
          esc_html(PHP_VERSION)
        );
        echo '<div class="notice notice-error"><p>' . $message . '</p></div>';
      });
    }

    // Check WordPress version.
    if (version_compare($wp_version, $required_wp_version, '<')) {
      add_action('admin_notices', function () use ($required_wp_version, $wp_version) {
        $message = sprintf(
        /* translators: %1$s: Required WordPress version, %2$s: Current WordPress version */
          esc_html__('SimplePostLike requires WordPress version %1$s or higher. You are running version %2$s.', 'simple-post-like'),
          esc_html($required_wp_version),
          esc_html($wp_version)
        );
        echo '<div class="notice notice-error"><p>' . $message . '</p></div>';
      });
    }
  }

  /**
   * Record the installation timestamp.
   */
  protected static function record_installation(): void {
    if (!get_option(self::INSTALLED_OPTION)) {
      update_option(self::INSTALLED_OPTION, time());
    }
  }

  /**
   * Update routine for version 1.1.0.
   */
  protected static function update_to_1_1_0(): void {
    // Future updates will go here
  }

  /**
   * Update routine for version 1.2.0.
   */
  protected static function update_to_1_2_0(): void {
    // Future updates will go here
  }

  /**
   * Log errors during installation or updates.
   *
   * @param string $message The error message to log.
   */
  protected static function log_error(string $message): void {
    if (function_exists('error_log')) {
      error_log('SimplePostLike Error: ' . $message);
    }
  }
}