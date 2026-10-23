<?php

defined('WP_UNINSTALL_PLUGIN') || exit;

/**
 * Delete the plugin options and like data for the current site.
 */
function simple_post_like_uninstall_site(): void {
  delete_option('simple_post_like_settings');
  delete_option('simple_post_like_version');
  delete_option('simple_post_like_installed');

  delete_post_meta_by_key('_simple_post_like_count');
  delete_post_meta_by_key('_simple_post_like_users');
  delete_post_meta_by_key('_simple_post_like_ips');
}

/**
 * Run the cleanup on every site of a multisite network, or on the single site.
 */
function simple_post_like_uninstall(): void {
  if (!is_multisite()) {
    simple_post_like_uninstall_site();
    return;
  }

  $site_ids = get_sites(['fields' => 'ids', 'number' => 0]);

  foreach ($site_ids as $site_id) {
    switch_to_blog($site_id);
    simple_post_like_uninstall_site();
    restore_current_blog();
  }
}

simple_post_like_uninstall();