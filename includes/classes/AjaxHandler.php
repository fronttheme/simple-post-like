<?php

	namespace FrontTheme\SimplePostLike;

	use FrontTheme\SimplePostLike\Traits\Singleton;

	defined( 'ABSPATH' ) || exit;

	class AjaxHandler {
		use Singleton;

		protected function init(): void {
			add_action( 'wp_ajax_simple_post_like', [ $this, 'handle_like_action' ] );
			add_action( 'wp_ajax_nopriv_simple_post_like', [ $this, 'handle_like_action' ] );
		}

		public function handle_like_action(): void {
			check_ajax_referer( 'simple_post_like_nonce', 'nonce' );

			$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

			if ( ! $post_id || ! get_post( $post_id ) ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Invalid post.', 'simple-post-like' ) ] );
			}

			// Block guests if disabled in settings.
			if ( ! is_user_logged_in() && ! Settings::instance()->get( 'allow_guests', true ) ) {
				wp_send_json_error( [ 'message' => esc_html__( 'You must be logged in to like posts.', 'simple-post-like' ) ] );
			}

			// Load all like data once.
			$like_count  = (int) get_post_meta( $post_id, '_simple_post_like_count', true );
			$liked_users = get_post_meta( $post_id, '_simple_post_like_users', true ) ?: [];
			$liked_ips   = get_post_meta( $post_id, '_simple_post_like_ips', true ) ?: [];

			if ( is_user_logged_in() ) {
				$user_id   = get_current_user_id();
				$has_liked = in_array( $user_id, $liked_users, true );

				if ( $has_liked ) {
					$like_count --;
					$liked_users = array_values( array_diff( $liked_users, [ $user_id ] ) );
				} else {
					$like_count ++;
					$liked_users[] = $user_id;

					// If user previously liked as guest, clean up the IP entry.
					$hashed_ip = LikeButton::instance()->get_hashed_ip();
					$liked_ips = array_values( array_diff( $liked_ips, [ $hashed_ip ] ) );
				}
			} else {
				// Guest: use hashed IP.
				$hashed_ip = LikeButton::instance()->get_hashed_ip();
				$has_liked = in_array( $hashed_ip, $liked_ips, true );

				if ( $has_liked ) {
					$like_count --;
					$liked_ips = array_values( array_diff( $liked_ips, [ $hashed_ip ] ) );
				} else {
					$like_count ++;
					$liked_ips[] = $hashed_ip;
				}
			}

			// Persist all changes.
			update_post_meta( $post_id, '_simple_post_like_count', max( 0, $like_count ) );
			update_post_meta( $post_id, '_simple_post_like_users', $liked_users );
			update_post_meta( $post_id, '_simple_post_like_ips', $liked_ips );

			$like_button = LikeButton::instance();

			wp_send_json_success( [
				'like_count'           => max( 0, $like_count ),
				'like_count_formatted' => $like_button->format_like_count( max( 0, $like_count ) ),
				'has_liked'            => ! $has_liked, // Toggled state.
			] );
		}
	}