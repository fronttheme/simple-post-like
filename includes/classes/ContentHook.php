<?php

	namespace FrontTheme\SimplePostLike;

	use FrontTheme\SimplePostLike\Traits\Singleton;

	defined('ABSPATH') || exit;

	/**
	 * Automatically injects the like button into post content
	 * based on the placement settings configured in the admin.
	 *
	 * Placement logic:
	 *  - auto_inject = false  → nothing, shortcode-only mode.
	 *  - inject_single        → 'before' | 'after' | 'both' | 'none' on singular views.
	 *  - inject_archive       → appends after content on archive/loop pages.
	 */
	class ContentHook {
		use Singleton;

		protected function init(): void {
			add_filter('the_content', [$this, 'inject']);
		}

		/**
		 * Filter callback — injects the button based on current context and settings.
		 *
		 * @param string $content Original post content.
		 * @return string Modified content.
		 */
		public function inject(string $content): string {
			// Must be inside the main loop.
			if (!in_the_loop() || !is_main_query()) {
				return $content;
			}

			$settings = Settings::instance();

			// Master toggle — bail immediately if auto inject is disabled.
			if (!$settings->get('auto_inject', true)) {
				return $content;
			}

			$post_id       = get_the_ID();
			$enabled_types = apply_filters( 'spl_allowed_post_types', Settings::instance()->get( 'post_types', ['post'] ) );
			$current_type  = get_post_type($post_id);

			// Only inject on post types the user has enabled.
			if (!in_array($current_type, $enabled_types, true)) {
				return $content;
			}

			$button = '<div class="spl-auto-inject">'
			          . LikeButton::instance()->get_like_button_html($post_id)
			          . '</div>';

			// Archive / loop context (blog page, category, tag, search, etc.).
			if (!is_singular()) {
				if ($settings->get('inject_archive', true)) {
					return $content . $button;
				}
				return $content;
			}

			// Singular context — use inject_single placement.
			$placement = $settings->get('inject_single', 'after');

			return match ($placement) {
				'before' => $button . $content,
				'after'  => $content . $button,
				'both'   => $button . $content . $button,
				default  => $content, // 'none' or any unexpected value.
			};
		}
	}