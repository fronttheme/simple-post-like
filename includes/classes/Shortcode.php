<?php

namespace FrontTheme\SimplePostLike;

use FrontTheme\SimplePostLike\Traits\Singleton;

defined('ABSPATH') || exit;

class Shortcode {
  use Singleton;

  protected function init(): void {
    add_shortcode('simple_post_like', [$this, 'shortcode_handler']);
  }

  public function shortcode_handler($atts): string {
    $atts = shortcode_atts([
      'post_id' => get_the_ID(),
      'style'   => 'button_default', // Default style
    ], $atts, 'simple_post_like');

    $post_id = intval($atts['post_id']);
    $style = sanitize_text_field($atts['style']);

    // Validation: Check if post exists
    if (!$post_id || !get_post($post_id)) {
      return '';
    }

    // Pass the style parameter to the like button
    return LikeButton::instance()->get_like_button_html($post_id, $style);
  }

}