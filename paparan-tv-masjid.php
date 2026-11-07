<?php
/**
 * Plugin Name:       Paparan TV Masjid
 * Plugin URI:        https://github.com/mohdneotech/paparan-tv-masjid
 * Description:       Paparan skrin penuh untuk TV masjid / surau di /tv/ — jam, waktu solat JAKIM (pilih zon) dengan waktu semasa & seterusnya serta kiraan detik, skrin azan & iqamah, slaid makluman, Infaq (DuitNow QR + akaun bank) dan pengumuman bergerak. Diurus dari wp-admin; TV dikemas kini sendiri.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            Mohd Nordin Hussain
 * Author URI:        https://mohdnordin.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       paparan-tv-masjid
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Another copy (e.g. the older site-specific "Perepat Paparan TV" build) is already loaded — stand down instead of fataling.
if ( defined( 'PTV_VER' ) ) {
	register_activation_hook( __FILE__, function () {
		wp_die( 'Sila nyahaktifkan plugin lama (Perepat Paparan TV) dahulu, kemudian aktifkan plugin ini. Tetapan akan dikekalkan.', 'Plugin lama masih aktif', [ 'back_link' => true ] );
	} );
	return;
}

define( 'PTV_VER', '1.0.0' );
define( 'PTV_OPT', 'ptv_settings' );
define( 'PTV_CAP', 'ptv_urus' );
define( 'PTV_FILE', __FILE__ );
define( 'PTV_URL', plugin_dir_url( __FILE__ ) );
define( 'PTV_REPO', 'mohdneotech/paparan-tv-masjid' );
define( 'PTV_SLUG', apply_filters( 'ptv_slug', 'tv' ) );

require_once __DIR__ . '/inc/data.php';
require_once __DIR__ . '/inc/waktu.php';
require_once __DIR__ . '/inc/class-github-updater.php';
if ( is_admin() ) { require_once __DIR__ . '/inc/admin.php'; }
if ( is_admin() || wp_doing_cron() ) { new PTV_GitHub_Updater( __FILE__, PTV_REPO, PTV_VER ); }

/* ---------- Capability ---------- */
function ptv_grant_caps() {
	foreach ( (array) apply_filters( 'ptv_roles', [ 'administrator', 'editor', 'pk_ajk' ] ) as $r ) {
		$role = get_role( $r );
		if ( $role && ! $role->has_cap( PTV_CAP ) ) $role->add_cap( PTV_CAP );
	}
	update_option( 'ptv_caps_ver', PTV_VER, false );
}
add_action( 'admin_init', function () { if ( get_option( 'ptv_caps_ver' ) !== PTV_VER ) ptv_grant_caps(); } );

/* ---------- /tv/ route ---------- */
add_action( 'init', function () {
	add_rewrite_rule( '^' . PTV_SLUG . '/?$', 'index.php?ptv_skrin=1', 'top' );
} );
add_filter( 'query_vars', function ( $v ) { $v[] = 'ptv_skrin'; return $v; } );

register_activation_hook( __FILE__, function () {
	add_rewrite_rule( '^' . PTV_SLUG . '/?$', 'index.php?ptv_skrin=1', 'top' );
	flush_rewrite_rules( false );
	ptv_grant_caps();
	if ( false === get_option( PTV_OPT ) ) add_option( PTV_OPT, ptv_defaults(), '', false );
	set_transient( 'ptv_welcome', 1, HOUR_IN_SECONDS );
} );
register_deactivation_hook( __FILE__, function () {
	flush_rewrite_rules( false );
	wp_clear_scheduled_hook( 'ptv_ws_cron' );
} );

/* Don't let WP canonical-redirect /tv to some other URL */
add_filter( 'redirect_canonical', function ( $url ) { return get_query_var( 'ptv_skrin' ) ? false : $url; } );

add_action( 'template_redirect', function () {
	if ( ! get_query_var( 'ptv_skrin' ) ) return;
	nocache_headers();
	header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
	header( 'X-Robots-Tag: noindex, nofollow' );
	header( 'Content-Type: text/html; charset=UTF-8' );
	status_header( 200 );
	$ptv_data = ptv_payload();
	include __DIR__ . '/templates/tv.php';
	exit;
} );

/* ---------- REST: /wp-json/paparan-tv/v1/data ---------- */
add_action( 'rest_api_init', function () {
	register_rest_route( 'paparan-tv/v1', '/data', [
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'callback'            => function () {
			$r = new WP_REST_Response( ptv_payload() );
			$r->header( 'Cache-Control', 'no-store, max-age=0' );
			$r->header( 'X-Robots-Tag', 'noindex' );
			return $r;
		},
	] );
} );

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $l ) {
	array_unshift( $l, '<a href="' . esc_url( admin_url( 'admin.php?page=paparan-tv' ) ) . '">Tetapan</a>', '<a href="' . esc_url( home_url( '/' . PTV_SLUG . '/' ) ) . '" target="_blank">Buka /' . esc_html( PTV_SLUG ) . '/</a>' );
	return $l;
} );
