<?php

use FrontTheme\SimplePostLike\LikeButton;

defined('ABSPATH') || exit;

/**
 * Render the like button for a post.
 *
 * @param int $post_id Post ID. Defaults to the current post.
 * @param string $style Display style override. Empty string uses the global setting.
 *
 * @return string Like button HTML, or an empty string when there is no post.
 */
function simple_post_like_button(int $post_id = 0, string $style = ''): string {
  if ($post_id <= 0) {
    $post_id = (int)get_the_ID();
  }

  if ($post_id <= 0 || !is_post_publicly_viewable($post_id)) {
    return '';
  }

  $override = $style !== '' ? $style : null;

  return LikeButton::instance()->get_like_button_html($post_id, $override);
}