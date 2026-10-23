<?php

namespace FrontTheme\SimplePostLike;

use FrontTheme\SimplePostLike\Traits\Singleton;

defined('ABSPATH') || exit;

/**
 * Handles plugin activation, deactivation and version updates.
 */
class Install {
  use Singleton;

  /**
   * Option name for the installed plugin version.
   */
  const VERSION_OPTION = 'simple_post_like_version';

  /**
   * Option name for the installation timestamp.
   */
  const INSTALLED_OPTION = 'simple_post_like_installed';

  protected function init(): void {
    add_action('init', [$this, 'check_version'], 5);
  }

  /**
   * Store the new version when the plugin files are newer than the saved version.
   */
  public function check_version(): void {
    $installed_version = get_option(self::VERSION_OPTION, '0.0.0');

    if (version_compare($installed_version, SIMPLE_POST_LIKE_VERSION, '>=')) {
      return;
    }

    $this->record_installation();
    update_option(self::VERSION_OPTION, SIMPLE_POST_LIKE_VERSION);
  }

  /**
   * Activation hook.
   */
  public function activate(): void {
    $this->record_installation();
    add_option(self::VERSION_OPTION, SIMPLE_POST_LIKE_VERSION);

    do_action('simple_post_like_activated');
  }

  /**
   * Deactivation hook.
   */
  public function deactivate(): void {
    do_action('simple_post_like_deactivated');
  }

  /**
   * Save the installation timestamp once.
   */
  private function record_installation(): void {
    add_option(self::INSTALLED_OPTION, time());
  }
}