<?php
/** Runs when the plugin is DELETED from wp-admin: removes settings, cached prayer times and the schedule. */
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;
global $wpdb;
wp_clear_scheduled_hook( 'ptv_ws_cron' );
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'ptv\_ws\_%' OR option_name LIKE '\_transient\_ptv\_%' OR option_name LIKE '\_transient\_timeout\_ptv\_%'" );
foreach ( [ 'ptv_settings', 'ptv_caps_ver' ] as $o ) { delete_option( $o ); }
delete_site_transient( 'ptv_gh_release' );
foreach ( wp_roles()->role_objects as $role ) { $role->remove_cap( 'ptv_urus' ); }
