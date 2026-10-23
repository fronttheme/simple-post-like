<?php

namespace FrontTheme\SimplePostLike;

use FrontTheme\SimplePostLike\Traits\Singleton;

defined('ABSPATH') || exit;

class AjaxHandler {
  use Singleton;

  protected function init(): void {
    add_action('wp_ajax_simple_post_like', [$this, 'handle_like_action']);
    add_action('wp_ajax_nopriv_simple_post_like', [$this, 'handle_like_action']);
  }

  public function handle_like_action(): void {
    check_ajax_referer('simple_post_like_nonce', 'nonce');

    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;

    if (!$post_id || !is_post_publicly_viewable($post_id)) {
      wp_send_json_error(['message' => __('Invalid post.', 'simple-post-like')]);
    }

    $is_guest      = !is_user_logged_in();
    $guest_allowed = apply_filters('simple_post_like_guest_likes_enabled', Settings::instance()->get('allow_guests', true));

    if ($is_guest && !$guest_allowed) {
      wp_send_json_error(['message' => __('You must be logged in to like posts.', 'simple-post-like')]);
    }

    $user_id     = get_current_user_id();
    $hashed_ip   = $is_guest ? LikeButton::instance()->get_hashed_ip() : '';
    $liked_users = $this->get_meta_list($post_id, '_simple_post_like_users');
    $liked_ips   = $this->get_meta_list($post_id, '_simple_post_like_ips');

    if ($is_guest) {
      $has_liked = in_array($hashed_ip, $liked_ips, true);

      if ($has_liked) {
        $liked_ips = array_values(array_diff($liked_ips, [$hashed_ip]));
      } else {
        $liked_ips[] = $hashed_ip;
      }
    } else {
      $has_liked = in_array($user_id, $liked_users, true);

      if ($has_liked) {
        $liked_users = array_values(array_diff($liked_users, [$user_id]));
      } else {
        $liked_users[] = $user_id;
      }
    }

    $like_count = count($liked_users) + count($liked_ips);

    do_action('simple_post_like_before_like', $post_id, $user_id, $hashed_ip);

    update_post_meta($post_id, '_simple_post_like_count', $like_count);
    update_post_meta($post_id, '_simple_post_like_users', $liked_users);
    update_post_meta($post_id, '_simple_post_like_ips', $liked_ips);

    Stats::instance()->flush_cache();

    if ($has_liked) {
      do_action('simple_post_like_after_unlike', $post_id, $like_count, $user_id);
    } else {
      do_action('simple_post_like_after_like', $post_id, $like_count, $user_id);
    }

    wp_send_json_success([
      'like_count'           => $like_count,
      'like_count_formatted' => LikeButton::instance()->format_like_count($like_count),
      'like_count_text'      => _n('Like', 'Likes', $like_count, 'simple-post-like'),
      'has_liked'            => !$has_liked,
    ]);
  }

  /**
   * Get a list stored in post meta, or an empty array.
   */
  private function get_meta_list(int $post_id, string $meta_key): array {
    $list = get_post_meta($post_id, $meta_key, true);

    return is_array($list) ? $list : [];
  }
}