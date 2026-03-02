<?php
/**
 * Runs when the plugin is deleted via the WordPress admin.
 * Removes all options stored by WP-Watchdog.
 *
 * @package WP_Watchdog
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'update_watchdog_token' );
