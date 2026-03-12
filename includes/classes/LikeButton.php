<?php

	namespace FrontTheme\SimplePostLike;

	use FrontTheme\SimplePostLike\Traits\Singleton;

	defined( 'ABSPATH' ) || exit;

	class LikeButton {
		use Singleton;

		public function get_like_button_html( int $post_id, ?string $override_style = null ): string {
			// Use override style if provided (via shortcode), otherwise use saved setting.
			$display_type = $override_style ?? Settings::instance()->get( 'display_type', 'button_default' );

			// Get like count once and cast to integer.
			$like_count = (int) get_post_meta( $post_id, '_simple_post_like_count', true );

			// Determine if the current user has liked the post.
			$user_has_liked = $this->check_user_has_liked( $post_id );

			// Build CSS classes.
			$wrapper_class = $this->build_wrapper_classes( $user_has_liked, $display_type );

			// Generate dynamic content.
			$like_icon    = $this->get_like_icon( $user_has_liked );
			$button_title = $user_has_liked
				? esc_html__( 'Unlike', 'simple-post-like' )
				: esc_html__( 'Like', 'simple-post-like' );
			$like_text    = $like_count === 1
				? esc_html__( 'Like', 'simple-post-like' )
				: esc_html__( 'Likes', 'simple-post-like' );

			return $this->render_like_button_html(
				$wrapper_class,
				$post_id,
				$like_count,
				$like_text,
				$like_icon,
				$button_title,
				$display_type
			);
		}

		private function check_user_has_liked( int $post_id ): bool {
			if ( is_user_logged_in() ) {
				$user_id     = get_current_user_id();
				$liked_users = get_post_meta( $post_id, '_simple_post_like_users', true ) ?: [];

				return in_array( $user_id, $liked_users, true );
			}

			// Guest: compare against hashed IP.
			$hashed_ip = $this->get_hashed_ip();
			$liked_ips = get_post_meta( $post_id, '_simple_post_like_ips', true ) ?: [];

			return in_array( $hashed_ip, $liked_ips, true );
		}

		private function build_wrapper_classes( bool $user_has_liked, string $display_type ): string {
			$classes = [ 'simple-post-like-button', esc_attr( $display_type ) ];

			if ( $user_has_liked ) {
				$classes[] = 'post-liked';
			}

			return implode( ' ', $classes );
		}

		private function get_like_icon( bool $user_has_liked ): string {
			$icon_class = $user_has_liked ? 'fa-solid' : 'fa-regular';

			return '<i class="' . esc_attr( $icon_class ) . ' fa-heart" aria-hidden="true"></i>';
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
			$formatted_count = $this->format_like_count( $like_count );

			$html = '<div class="' . esc_attr( $wrapper_class ) . '" data-post-id="' . esc_attr( (string) $post_id ) . '">';
			$html .= $this->render_like_count_section( $formatted_count, $like_text, $display_type );
			$html .= $this->render_like_button( $like_icon, $button_title, $display_type );
			$html .= '</div>';

			return $html;
		}

		private function render_like_count_section( string $formatted_count, string $like_text, string $display_type ): string {
			// icon_only hides the count visually but keeps it in DOM for JS updates.
			if ( $display_type === 'icon_only' ) {
				return '<span class="like-count" aria-hidden="true" hidden>' . esc_html( $formatted_count ) . '</span>';
			}

			$html = '';

			if ( $display_type === 'button_default' ) {
				$html .= '<div class="like-count-holder">';
			}

			$html .= '<span class="like-count">' . esc_html( $formatted_count ) . '</span>';

			if ( $display_type === 'button_default' ) {
				$html .= '<span class="like-count-text"> ' . esc_html( $like_text ) . '</span>';
				$html .= '</div>';
			}

			return $html;
		}

		private function render_like_button( string $like_icon, string $button_title, string $display_type ): string {
			$html = '<button class="like-btn" title="' . esc_attr( $button_title ) . '" aria-label="' . esc_attr( $button_title ) . '">';
			$html .= '<span class="button-content">' . $like_icon;

			if ( $display_type === 'button_default' ) {
				$html .= '<span class="btn-label"> ' . esc_html( $button_title ) . '</span>';
			}

			$html .= '</span></button>';

			return $html;
		}

		/**
		 * Format large numbers for display (1000 → 1K, 1000000 → 1M).
		 */
		public function format_like_count( int $number ): string {
			if ( $number >= 1_000_000 ) {
				return round( $number / 1_000_000, 1 ) . 'M';
			}

			if ( $number >= 1_000 ) {
				return round( $number / 1_000, 1 ) . 'K';
			}

			return (string) $number;
		}

		/**
		 * Return a hashed IP for guest like tracking (GDPR-friendly).
		 */
		public function get_hashed_ip(): string {
			$ip = $_SERVER['REMOTE_ADDR'] ?? '';

			return hash( 'sha256', $ip . NONCE_SALT );
		}
	}