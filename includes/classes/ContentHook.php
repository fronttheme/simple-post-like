<?php

namespace FrontTheme\SimplePostLike;

use FrontTheme\SimplePostLike\Traits\Singleton;

defined('ABSPATH') || exit;

/**
 * Inserts the like button into post content based on the plugin settings.
 */
class ContentHook {
  use Singleton;

  protected function init(): void {
    add_filter('the_content', [$this, 'inject']);
  }

  /**
   * Add the like button before and/or after the post content.
   *
   * @param string $content Post content.
   *
   * @return string Post content with the like button.
   */
  public function inject(string $content): string {
    $post_id = (int)get_the_ID();
    $context = is_singular() ? 'single' : 'archive';

    if (!$this->should_auto_inject($context, $post_id)) {
      return $content;
    }

    $button = '<div class="spl-auto-inject">'
      . LikeButton::instance()->get_like_button_html($post_id)
      . '</div>';

    if ($context === 'archive') {
      return $content . $button;
    }

    return match (Settings::instance()->get('inject_single', 'after')) {
      'before' => $button . $content,
      'both' => $button . $content . $button,
      default => $content . $button,
    };
  }

  /**
   * Decide whether the like button is inserted automatically.
   *
   * @param string $context 'single' or 'archive'.
   * @param int $post_id Post ID.
   *
   * @return bool True when the button should be inserted.
   */
  public function should_auto_inject(string $context, int $post_id): bool {
    if ($post_id <= 0 || !in_the_loop() || !is_main_query()) {
      return false;
    }

    $settings = Settings::instance();

    if (!$settings->get('auto_inject', true)) {
      return false;
    }

    $post_types = apply_filters('simple_post_like_allowed_post_types', $settings->get('post_types', ['post']));

    if (!in_array(get_post_type($post_id), $post_types, true)) {
      return false;
    }

    if ($context === 'archive' && !$settings->get('inject_archive', true)) {
      return false;
    }

    if ($context === 'single' && $settings->get('inject_single', 'after') === 'none') {
      return false;
    }

    /**
     * Filters whether the like button is inserted automatically.
     *
     * @param bool $enabled True unless the theme declares support for simple-post-like.
     * @param string $context 'single' or 'archive'.
     * @param int $post_id Post ID.
     */
    return (bool)apply_filters('simple_post_like_auto_inject', !$this->theme_places_button(), $context, $post_id);
  }

  /**
   * Check whether the active theme prints the like button itself.
   */
  public function theme_places_button(): bool {
    return current_theme_supports('simple-post-like');
  }
}