<?php

	namespace FrontTheme\SimplePostLike;

	use FrontTheme\SimplePostLike\Traits\Singleton;

	defined('ABSPATH') || exit;

	/**
	 * Manages plugin settings stored in WordPress options.
	 * Replaces all theme-specific option dependencies.
	 */
	class Settings {
		use Singleton;

		/**
		 * The option key used to store all plugin settings.
		 */
		const OPTION_KEY = 'simple_post_like_settings';

		/**
		 * Default settings values.
		 */
		const DEFAULTS = [
			'display_type'   => 'button_default', // button_default | icon_only | icon_counter
			'post_types'     => ['post'],          // Post types where the like button is enabled.
			'allow_guests'   => true,              // Allow non-logged-in users to like posts.

			// Auto injection placement.
			'auto_inject'    => true,             // Master toggle. false = shortcode-only mode.
			'inject_single'  => 'after',          // Singular view: 'before' | 'after' | 'both' | 'none'.
			'inject_archive' => true,             // Also inject in archive/loop post items.
		];

		/**
		 * Cached settings to avoid repeated DB reads.
		 */
		private array $settings = [];

		protected function init(): void {
			$this->settings = $this->load();
		}

		/**
		 * Load settings from the database, merged with defaults.
		 */
		private function load(): array {
			$saved = get_option(self::OPTION_KEY, []);
			return wp_parse_args($saved, self::DEFAULTS);
		}

		/**
		 * Get a single setting value by key.
		 *
		 * @param string $key     The setting key.
		 * @param mixed  $default Fallback if key doesn't exist.
		 * @return mixed
		 */
		public function get(string $key, mixed $default = null): mixed {
			return $this->settings[$key] ?? $default;
		}

		/**
		 * Get all settings.
		 */
		public function all(): array {
			return $this->settings;
		}

		/**
		 * Save settings to the database.
		 *
		 * @param array $data Raw input data (will be sanitized).
		 */
		public function save(array $data): bool {
			$sanitized = $this->sanitize($data);
			$saved = update_option(self::OPTION_KEY, $sanitized);

			if ($saved) {
				$this->settings = wp_parse_args($sanitized, self::DEFAULTS);
			}

			return $saved;
		}

		/**
		 * Sanitize raw settings input.
		 *
		 * @param array $data Raw input.
		 * @return array Sanitized settings.
		 */
		private function sanitize(array $data): array {
			$allowed_display_types = ['button_default', 'icon_only', 'icon_counter'];

			$display_type = sanitize_text_field($data['display_type'] ?? '');
			if (!in_array($display_type, $allowed_display_types, true)) {
				$display_type = self::DEFAULTS['display_type'];
			}

			$post_types = [];
			if (!empty($data['post_types']) && is_array($data['post_types'])) {
				foreach ($data['post_types'] as $pt) {
					$sanitized_pt = sanitize_key($pt);
					if (post_type_exists($sanitized_pt)) {
						$post_types[] = $sanitized_pt;
					}
				}
			}
			if (empty($post_types)) {
				$post_types = self::DEFAULTS['post_types'];
			}

			$allowed_inject_single = ['before', 'after', 'both', 'none'];
			$inject_single = sanitize_text_field($data['inject_single'] ?? '');
			if (!in_array($inject_single, $allowed_inject_single, true)) {
				$inject_single = self::DEFAULTS['inject_single'];
			}

			return [
				'display_type'   => $display_type,
				'post_types'     => $post_types,
				'allow_guests'   => !empty($data['allow_guests']),
				'auto_inject'    => !empty($data['auto_inject']),
				'inject_single'  => $inject_single,
				'inject_archive' => !empty($data['inject_archive']),
			];
		}

		/**
		 * Delete all plugin settings from the database.
		 */
		public function delete(): void {
			delete_option(self::OPTION_KEY);
			$this->settings = self::DEFAULTS;
		}
	}