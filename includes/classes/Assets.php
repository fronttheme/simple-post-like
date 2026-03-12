<?php

	namespace FrontTheme\SimplePostLike;

	use FrontTheme\SimplePostLike\Traits\Singleton;

	defined('ABSPATH') || exit;

	class Assets {
		use Singleton;

		protected function init(): void {
			add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
		}

		public function enqueue_scripts(): void {
			$settings = Settings::instance();

			wp_enqueue_script(
				'simple-post-like-js',
				SIMPLE_POST_LIKE_URL . 'assets/js/simple-post-like.js',
				[],
				SIMPLE_POST_LIKE_VERSION,
				true
			);

			wp_enqueue_style(
				'simple-post-like-css',
				SIMPLE_POST_LIKE_URL . 'assets/css/simple-post-like.css',
				[],
				SIMPLE_POST_LIKE_VERSION
			);

			// Enqueue Font Awesome only if the active theme hasn't already loaded it.
			if (!wp_style_is('font-awesome', 'registered') && !wp_style_is('font-awesome', 'enqueued')) {
				wp_enqueue_style(
					'simple-post-like-font-awesome',
					'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css',
					[],
					'6.5.0'
				);
			}

			wp_localize_script('simple-post-like-js', 'simplePostLike', [
				'ajax_url'       => admin_url('admin-ajax.php'),
				'nonce'          => wp_create_nonce('simple_post_like_nonce'),
				'button_display' => $settings->get('display_type', 'button_default'),
				'button_text'    => [
					'text_like'    => esc_html__('Like', 'simple-post-like'),
					'text_unlike'  => esc_html__('Unlike', 'simple-post-like'),
					'title_like'   => esc_html__('I like this', 'simple-post-like'),
					'title_unlike' => esc_html__('Unlike', 'simple-post-like'),
					'catch_alert'  => esc_html__('Something went wrong. Please try again.', 'simple-post-like'),
				],
			]);
		}
	}