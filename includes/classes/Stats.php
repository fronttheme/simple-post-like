<?php

	namespace FrontTheme\SimplePostLike;

	use FrontTheme\SimplePostLike\Traits\Singleton;

	defined( 'ABSPATH' ) || exit;

	/**
	 * Provides like statistics for the admin dashboard.
	 *
	 * - Total likes across all posts.
	 * - Most liked posts (top N).
	 * - Per-post like count column in the Posts list table.
	 */
	class Stats {
		use Singleton;

		protected function init(): void {
			// Inject like count column into enabled post type list tables.
			add_action( 'admin_init', [ $this, 'register_post_columns' ] );
		}

		/**
		 * Register like count columns for all enabled post types.
		 */
		public function register_post_columns(): void {
			$post_types = Settings::instance()->get( 'post_types', [ 'post' ] );

			foreach ( $post_types as $pt ) {
				add_filter( "manage_{$pt}_posts_columns", [ $this, 'add_likes_column' ] );
				add_action( "manage_{$pt}_posts_custom_column", [ $this, 'render_likes_column' ], 10, 2 );
				add_filter( "manage_edit-{$pt}_sortable_columns", [ $this, 'make_likes_column_sortable' ] );
			}

			// Handle orderby for the likes column.
			add_action( 'pre_get_posts', [ $this, 'handle_likes_orderby' ] );
		}

		/**
		 * Add "Likes" column to the posts list table.
		 *
		 * @param array $columns Existing columns.
		 *
		 * @return array Modified columns.
		 */
		public function add_likes_column( array $columns ): array {
			// Insert before the date column for a natural position.
			$date = $columns['date'] ?? null;
			unset( $columns['date'] );

			$columns['spl_likes'] = '<span class="spl-col-icon dashicons dashicons-heart" title="'
			                        . esc_attr__( 'Likes', 'simple-post-like' ) . '"></span>'
			                        . '<span class="screen-reader-text">' . esc_html__( 'Likes', 'simple-post-like' ) . '</span>';

			if ( $date !== null ) {
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
		public function render_likes_column( string $column, int $post_id ): void {
			if ( $column !== 'spl_likes' ) {
				return;
			}

			$count = (int) get_post_meta( $post_id, '_simple_post_like_count', true );
			echo '<span class="spl-col-count">' . esc_html(
					LikeButton::instance()->format_like_count( $count )
				) . '</span>';
		}

		/**
		 * Make the likes column sortable.
		 *
		 * @param array $columns Sortable columns.
		 *
		 * @return array Modified sortable columns.
		 */
		public function make_likes_column_sortable( array $columns ): array {
			$columns['spl_likes'] = 'spl_likes';

			return $columns;
		}

		/**
		 * Handle ordering by likes count in WP_Query.
		 *
		 * @param \WP_Query $query Current query.
		 */
		public function handle_likes_orderby( \WP_Query $query ): void {
			if ( ! is_admin() || ! $query->is_main_query() ) {
				return;
			}

			if ( $query->get( 'orderby' ) === 'spl_likes' ) {
				$query->set( 'meta_key', '_simple_post_like_count' );
				$query->set( 'orderby', 'meta_value_num' );
			}
		}

		/**
		 * Get the total number of likes across all posts.
		 *
		 * @return int Total like count.
		 */
		public function get_total_likes(): int {
			global $wpdb;

			$total = $wpdb->get_var(
				"SELECT SUM(CAST(meta_value AS UNSIGNED))
       FROM {$wpdb->postmeta}
       WHERE meta_key = '_simple_post_like_count'
       AND meta_value != ''"
			);

			return (int) $total;
		}

		/**
		 * Get the most liked posts.
		 *
		 * @param int $limit Number of posts to return. Default 10.
		 *
		 * @return array Array of post objects with like_count property.
		 */
		public function get_most_liked_posts( int $limit = 10 ): array {
			$posts = get_posts( [
				'post_type'      => Settings::instance()->get( 'post_types', [ 'post' ] ),
				'post_status'    => 'publish',
				'posts_per_page' => $limit,
				'meta_key'       => '_simple_post_like_count',
				'meta_value'     => '0',
				'meta_compare'   => '>',
				'orderby'        => 'meta_value_num',
				'order'          => 'DESC',
				'no_found_rows'  => true,
			] );

			// Attach like count to each post object for easy access.
			foreach ( $posts as $post ) {
				$post->like_count = (int) get_post_meta( $post->ID, '_simple_post_like_count', true );
			}

			return $posts;
		}

		/**
		 * Get the total number of posts that have at least one like.
		 *
		 * @return int Count of liked posts.
		 */
		public function get_liked_posts_count(): int {
			global $wpdb;

			$count = $wpdb->get_var(
				"SELECT COUNT(*)
       FROM {$wpdb->postmeta}
       WHERE meta_key = '_simple_post_like_count'
       AND CAST(meta_value AS UNSIGNED) > 0"
			);

			return (int) $count;
		}
	}