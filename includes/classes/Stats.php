<?php

namespace FrontTheme\SimplePostLike;

use FrontTheme\SimplePostLike\Traits\Singleton;
use WP_Post;
use WP_Query;

defined('ABSPATH') || exit;

/**
 * Provides like statistics for the admin dashboard.
 *
 * - Total likes across all posts.
 * - Most liked posts (top N).
 * - Per-post like count column in the Posts list table.
 */
class Stats {
  use Singleton;

  private const CACHE_GROUP = 'simple_post_like';

  protected function init(): void {
    // Inject like count column into enabled post type list tables.
    add_action('admin_init', [$this, 'register_post_columns']);
  }

  /**
   * Register like count columns for all enabled post types.
   */
  public function register_post_columns(): void {
    $post_types = apply_filters('simple_post_like_allowed_post_types', Settings::instance()->get('post_types', ['post']));

    foreach ($post_types as $pt) {
      add_filter("manage_{$pt}_posts_columns", [$this, 'add_likes_column']);
      add_action("manage_{$pt}_posts_custom_column", [$this, 'render_likes_column'], 10, 2);
      add_filter("manage_edit-{$pt}_sortable_columns", [$this, 'make_likes_column_sortable']);
    }

    // Handle orderby for the likes column.
    add_action('pre_get_posts', [$this, 'handle_likes_orderby']);
  }

  /**
   * Add "Likes" column to the posts list table.
   *
   * @param array $columns Existing columns.
   *
   * @return array Modified columns.
   */
  public function add_likes_column(array $columns): array {
    // Insert before the date column for a natural position.
    $date = $columns['date'] ?? null;
    unset($columns['date']);

    $columns['spl_likes'] = '<span class="spl-col-icon dashicons dashicons-heart" title="'
      . esc_attr__('Likes', 'simple-post-like') . '"></span>'
      . '<span class="screen-reader-text">' . esc_html__('Likes', 'simple-post-like') . '</span>';

    if ($date !== null) {
      $columns['date'] = $date;
    }

    return $columns;
  }

  /**
   * Render the like count for each post row.
   *
   * @param string $column Column name.
   * @param int $post_id Current post ID.
   */
  public function render_likes_column(string $column, int $post_id): void {
    if ($column !== 'spl_likes') {
      return;
    }

    $count = $this->get_post_like_count($post_id);
    echo '<span class="spl-col-count">' . esc_html(
        LikeButton::instance()->format_like_count($count)
      ) . '</span>';
  }

  /**
   * Make the likes column sortable.
   *
   * @param array $columns Sortable columns.
   *
   * @return array Modified sortable columns.
   */
  public function make_likes_column_sortable(array $columns): array {
    $columns['spl_likes'] = 'spl_likes';

    return $columns;
  }

  /**
   * Sort the posts list by like count, keeping posts that have no likes yet.
   *
   * @param WP_Query $query Current query.
   */
  public function handle_likes_orderby(WP_Query $query): void {
    if (!is_admin() || !$query->is_main_query()) {
      return;
    }

    if ($query->get('orderby') !== 'spl_likes') {
      return;
    }

    // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Admin list table sorting only.
    $query->set('meta_query', [
      'relation'   => 'OR',
      'like_count' => [
        'key'  => '_simple_post_like_count',
        'type' => 'NUMERIC',
      ],
      [
        'key'     => '_simple_post_like_count',
        'compare' => 'NOT EXISTS',
      ],
    ]);

    $query->set('orderby', 'like_count');
  }

  /**
   * Get the most liked published posts.
   *
   * @param int $limit Number of posts to return.
   *
   * @return WP_Post[]
   */
  public function get_most_liked_posts(int $limit = 10): array {
    return get_posts([
      'post_type'      => apply_filters('simple_post_like_allowed_post_types', Settings::instance()->get('post_types', ['post'])),
      'post_status'    => 'publish',
      'posts_per_page' => $limit,
      'meta_key'       => '_simple_post_like_count', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Admin-only top list.
      'meta_value'     => 0, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Admin-only top list.
      'meta_type'      => 'NUMERIC',
      'meta_compare'   => '>',
      'orderby'        => 'meta_value_num',
      'order'          => 'DESC',
      'no_found_rows'  => true,
    ]);
  }

  /**
   * Get the like count for one post.
   */
  public function get_post_like_count(int $post_id): int {
    return (int)get_post_meta($post_id, '_simple_post_like_count', true);
  }

  /**
   * Get the total number of likes across all posts.
   */
  public function get_total_likes(): int {
    return $this->get_totals()['likes'];
  }

  /**
   * Get the number of posts that have at least one like.
   */
  public function get_liked_posts_count(): int {
    return $this->get_totals()['posts'];
  }

  /**
   * Clear the cached totals.
   */
  public function flush_cache(): void {
    wp_cache_delete('totals', self::CACHE_GROUP);
  }

  /**
   * Get total likes and liked post count in one query.
   *
   * @return array{likes: int, posts: int}
   */
  private function get_totals(): array {
    $totals = wp_cache_get('totals', self::CACHE_GROUP);

    if (is_array($totals)) {
      return $totals;
    }

    global $wpdb;

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- WordPress has no API for summing meta values; the result is cached.
    $row = $wpdb->get_row(
      "SELECT SUM( CAST( meta_value AS UNSIGNED ) ) AS likes, COUNT(*) AS posts
				FROM {$wpdb->postmeta}
				WHERE meta_key = '_simple_post_like_count'
				AND CAST( meta_value AS UNSIGNED ) > 0"
    );

    $totals = [
      'likes' => (int)($row->likes ?? 0),
      'posts' => (int)($row->posts ?? 0),
    ];

    wp_cache_set('totals', $totals, self::CACHE_GROUP, HOUR_IN_SECONDS);

    return $totals;
  }

}