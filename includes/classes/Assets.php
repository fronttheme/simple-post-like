<?php

namespace FrontTheme\SimplePostLike;

use FrontTheme\SimplePostLike\Traits\Singleton;

defined('ABSPATH') || exit;

class Assets {
  use Singleton;

  protected function init(): void {
    add_action('wp_enqueue_scripts', [$this, 'register_assets']);
  }

  public function register_assets(): void {

    // Registered only; LikeButton enqueues it on pages that show a button.
    wp_register_script(
      'simple-post-like',
      SIMPLE_POST_LIKE_URL . 'assets/js/simple-post-like.js',
      [],
      SIMPLE_POST_LIKE_VERSION,
      ['in_footer' => true]
    );

    wp_localize_script('simple-post-like', 'simplePostLike', [
      'ajax_url' => admin_url('admin-ajax.php'),
      'nonce'    => wp_create_nonce('simple_post_like_nonce'),
      'text'     => [
        'like'   => __('Like', 'simple-post-like'),
        'unlike' => __('Unlike', 'simple-post-like'),
        'error'  => __('Something went wrong. Please try again.', 'simple-post-like'),
      ],
    ]);

    wp_enqueue_style(
      'simple-post-like',
      SIMPLE_POST_LIKE_URL . 'assets/css/simple-post-like.css',
      [],
      SIMPLE_POST_LIKE_VERSION
    );
  }
}