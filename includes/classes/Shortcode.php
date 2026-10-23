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
      'post_id' => 0,
      'style'   => '',
    ], $atts, 'simple_post_like');

    return simple_post_like_button(absint($atts['post_id']), sanitize_key($atts['style']));
  }

}