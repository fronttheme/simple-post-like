<?php

namespace FrontTheme\SimplePostLike;

use FrontTheme\SimplePostLike\Traits\Singleton;

defined('ABSPATH') || exit;

class LikeButton {
  use Singleton;

  public function get_like_button_html(int $post_id, ?string $override_style = null): string {
    // Load the like script only on pages that show a button.
    wp_enqueue_script('simple-post-like');

    $display_type   = $this->get_display_type($override_style);
    $like_count     = (int)get_post_meta($post_id, '_simple_post_like_count', true);
    $user_has_liked = $this->check_user_has_liked($post_id);
    $wrapper_class  = $this->build_wrapper_classes($user_has_liked, $display_type);

    $button_title = $user_has_liked
      ? __('Unlike', 'simple-post-like')
      : __('Like', 'simple-post-like');

    $like_text = _n('Like', 'Likes', $like_count, 'simple-post-like');

    return $this->render_like_button_html(
      $wrapper_class,
      $post_id,
      $like_count,
      $like_text,
      $this->get_like_icon(),
      $button_title,
      $display_type
    );
  }

  private function get_display_type(?string $override_style): string {
    if ($override_style !== null && in_array($override_style, Settings::DISPLAY_TYPES, true)) {
      return $override_style;
    }

    return Settings::instance()->get('display_type', 'button_default');
  }

  private function check_user_has_liked(int $post_id): bool {
    if (is_user_logged_in()) {
      $liked_users = get_post_meta($post_id, '_simple_post_like_users', true);

      return is_array($liked_users) && in_array(get_current_user_id(), $liked_users, true);
    }

    $liked_ips = get_post_meta($post_id, '_simple_post_like_ips', true);

    return is_array($liked_ips) && in_array($this->get_hashed_ip(), $liked_ips, true);
  }

  private function build_wrapper_classes(bool $user_has_liked, string $display_type): string {
    $classes = ['simple-post-like-button', $display_type];

    if ($user_has_liked) {
      $classes[] = 'post-liked';
    }

    return implode(' ', $classes);
  }

  private function get_like_icon(): string {
    return '<svg class="like-icon" viewBox="0 0 24 24" width="1em" height="1em" aria-hidden="true">'
      . '<path d="M12 20.5 4.2 12.7a4.8 4.8 0 0 1 6.8-6.8l1 1 1-1a4.8 4.8 0 0 1 6.8 6.8z"/>'
      . '</svg>';
  }

  private function render_like_button_html(
    string $wrapper_class,
    int $post_id,
    int $like_count,
    string $like_text,
    string $like_icon,
    string $button_title,
    string $display_type
  ): string {
    $formatted_count = $this->format_like_count($like_count);

    $html = '<div class="' . esc_attr($wrapper_class) . '" data-post-id="' . esc_attr((string)$post_id) . '">';
    $html .= $this->render_like_count_section($formatted_count, $like_text, $display_type);
    $html .= $this->render_like_button($like_icon, $button_title, $display_type);
    $html .= '</div>';

    return apply_filters('simple_post_like_button_html', $html, $post_id, $display_type);
  }

  private function render_like_count_section(string $formatted_count, string $like_text, string $display_type): string {
    // icon_only hides the count visually but keeps it in DOM for JS updates.
    if ($display_type === 'icon_only') {
      return '<span class="like-count" aria-hidden="true" hidden>' . esc_html($formatted_count) . '</span>';
    }

    $html = '';

    if ($display_type === 'button_default') {
      $html .= '<div class="like-count-holder">';
    }

    $html .= '<span class="like-count">' . esc_html($formatted_count) . '</span>';

    if ($display_type === 'button_default') {
      $html .= '<span class="like-count-text"> ' . esc_html($like_text) . '</span>';
      $html .= '</div>';
    }

    return $html;
  }

  private function render_like_button(string $like_icon, string $button_title, string $display_type): string {
    $html = '<button type="button" class="like-btn" title="' . esc_attr($button_title) . '" aria-label="' . esc_attr($button_title) . '">';
    $html .= '<span class="button-content">' . $like_icon;

    if ($display_type === 'button_default') {
      $html .= '<span class="btn-label"> ' . esc_html($button_title) . '</span>';
    }

    $html .= '</span></button>';

    return $html;
  }

  /**
   * Format large numbers for display (1000 → 1K, 1000000 → 1M).
   */
  public function format_like_count(int $number): string {
    $formatted = (string)$number;

    if ($number >= 1_000) {
      $formatted = round($number / 1_000, 1) . 'K';
    }

    // From 999,950 the K value would round up to "1000K", so switch to M.
    if ($number >= 999_950) {
      $formatted = round($number / 1_000_000, 1) . 'M';
    }

    return (string)apply_filters('simple_post_like_count_format', $formatted, $number);
  }

  /**
   * Return a hashed visitor IP for guest like tracking.
   */
  public function get_hashed_ip(): string {
    $ip = '';

    if (isset($_SERVER['REMOTE_ADDR'])) {
      $ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
    }

    /**
     * Filters the visitor IP used for guest likes.
     *
     * @param string $ip Visitor IP address.
     */
    $ip = (string)apply_filters('simple_post_like_guest_ip', $ip);

    return hash('sha256', $ip . NONCE_SALT);
  }
}