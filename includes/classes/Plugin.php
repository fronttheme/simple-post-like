<?php

	namespace FrontTheme\SimplePostLike;

	use FrontTheme\SimplePostLike\Traits\Singleton;

	defined( 'ABSPATH' ) || exit;

	final class Plugin {
		use Singleton;

		/**
		 * Using init() as the Singleton trait's template method —
		 * not __construct() — keeps the pattern consistent.
		 */
		protected function init(): void {
			Settings::instance();
			Assets::instance();
			AjaxHandler::instance();
			Shortcode::instance();
			ContentHook::instance();
			Stats::instance();
			Admin::instance();
			Install::instance();

			add_action( 'init', [ $this, 'load_textdomain' ] );
		}

		public function load_textdomain(): void {
			load_plugin_textdomain(
				'simple-post-like',
				false,
				dirname( plugin_basename( SIMPLE_POST_LIKE_FILE ) ) . '/languages'
			);
		}
	}