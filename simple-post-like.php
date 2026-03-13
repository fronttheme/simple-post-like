<?php

	/**
	 * Plugin Name: Simple Post Like
	 * Description: Add simple, intuitive reactions to your posts.
	 * Plugin URI: https://www.fronttheme.com/products/simple-post-like
	 * Version: 1.0.0
	 * Author: FrontTheme
	 * Author URI: https://www.fronttheme.com
	 * Text Domain: simple-post-like
	 * Domain Path: /languages/
	 * License: GPLv2 or later
	 * Copyright: FrontTheme
	 * Requires at least: 6.8
	 * Requires PHP: 8.0
	 */

	use FrontTheme\SimplePostLike\Install;
	use FrontTheme\SimplePostLike\Plugin;

	defined( 'ABSPATH' ) || exit;

	define( 'SIMPLE_POST_LIKE_FILE', __FILE__ );
	define( 'SIMPLE_POST_LIKE_VERSION', '1.0.0' );
	define( 'SIMPLE_POST_LIKE_PATH', plugin_dir_path( __FILE__ ) );
	define( 'SIMPLE_POST_LIKE_URL', plugin_dir_url( __FILE__ ) );

	// Load Composer autoloader.
	if ( file_exists( SIMPLE_POST_LIKE_PATH . 'vendor/autoload.php' ) ) {
		require_once SIMPLE_POST_LIKE_PATH . 'vendor/autoload.php';
	}

	// Initialize the plugin.
	Plugin::instance();

	/**
	 * Render the like button for a post.
	 *
	 * @param int $post_id Post ID. Defaults to current post.
	 * @param string $style Display style override. Empty string uses global setting.
	 *
	 * @return string Like button HTML.
	 */
	function spl_like_button( int $post_id = 0, string $style = '' ): string {
		$post_id  = $post_id > 0 ? $post_id : (int) get_the_ID();
		$override = $style !== '' ? $style : null;

		return \FrontTheme\SimplePostLike\LikeButton::instance()->get_like_button_html( $post_id, $override );
	}

	register_activation_hook( __FILE__, [ Install::instance(), 'activate' ] );
	register_deactivation_hook( __FILE__, [ Install::instance(), 'deactivate' ] );