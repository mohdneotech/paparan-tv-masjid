<?php
if ( ! defined( 'ABSPATH' ) ) exit;

const PTV_PRAYERS = [ 'imsak', 'subuh', 'syuruk', 'zohor', 'asar', 'maghrib', 'isyak' ];
const PTV_WAJIB   = [ 'subuh', 'zohor', 'asar', 'maghrib', 'isyak' ];

function ptv_defaults() {
	$nama = get_bloginfo( 'name' ) ?: 'Masjid';
	return [
		'zon'         => 'WLY01',            // JAKIM e-Solat zone
		'nama'        => $nama,
		'lokasi'      => '',
		'logo'        => '',                 // empty = built-in white masjid icon
		// marquee
		'speed'       => 90,               // px per second at 1920px stage width
		'label'       => 'Pengumuman',
		'lalai'       => 'Jom Rapatkan Saf! Selamat datang ke ' . $nama . '.',
		'items'       => [
			[ 't' => 'Jom Rapatkan Saf! Luruskan dan rapatkan saf ketika solat berjemaah.', 'mula' => '', 'tamat' => '', 'on' => 1, 'p' => 0 ],
		],
		// centre panel slides
		'slides'      => [
			[ 'h' => 'Selamat Datang', 'b' => 'Luruskan dan rapatkan saf. Matikan telefon bimbit semasa berada di dalam masjid.', 'mula' => '', 'tamat' => '', 'on' => 1 ],
		],
		'slide_sec'   => 15,
		// infaq
		'infaq_mode'  => 'slaid',          // slaid | tetap | tiada
		'infaq_tajuk' => 'Infaq & Sumbangan',
		'bank'        => '',
		'akaun'       => '',
		'nama_akaun'  => $nama,
		'qr'          => '',
		'infaq_nota'  => 'Imbas DuitNow QR menggunakan aplikasi perbankan anda.',
		// azan / iqamah
		'azan_on'     => 1,
		'iqamah'      => [ 'subuh' => 10, 'zohor' => 10, 'asar' => 10, 'maghrib' => 7, 'isyak' => 10 ],
		'solat_min'   => 10,               // "solat sedang didirikan" screen length
		'jumaat_min'  => 45,               // Friday khutbah + solat screen length
		'masa_12j'    => 1,
	];
}

function ptv_settings() {
	$s = get_option( PTV_OPT );
	$d = ptv_defaults();
	if ( ! is_array( $s ) ) return $d;
	$s = array_merge( $d, $s );
	$s['iqamah'] = array_merge( $d['iqamah'], (array) $s['iqamah'] );
	return $s;
}

/** Is a dated row live today? */
function ptv_live( $row, $today ) {
	if ( empty( $row['on'] ) ) return false;
	if ( ! empty( $row['mula'] ) && $today < $row['mula'] ) return false;
	if ( ! empty( $row['tamat'] ) && $today > $row['tamat'] ) return false;
	return true;
}

/** Prayer times for one day (JAKIM data for the chosen zone, see inc/waktu.php). */
function ptv_day( $date ) {
	$est = false;
	$row = ptv_waktu_tarikh( $date );
	if ( ! $row && $date === wp_date( 'Y-m-d' ) ) {
		$row = ptv_waktu_hari_ini(); $est = ! empty( $row['estimate'] );
	}
	if ( ! $row ) return null;
	$tz = wp_timezone(); $t = []; $hm = [];
	foreach ( PTV_PRAYERS as $k ) {
		$v = $row[ $k ] ?? '';
		if ( ! preg_match( '/^\d{2}:\d{2}$/', $v ) ) continue;
		$hm[ $k ] = $v;
		$t[ $k ]  = ( new DateTime( $date . ' ' . $v . ':00', $tz ) )->getTimestamp() * 1000;
	}
	return [
		'date'  => $date,
		'dow'   => (int) ( new DateTime( $date . ' 12:00', $tz ) )->format( 'w' ),
		'hijri' => ptv_hijri_label( $row['hijri'] ?? '' ),
		't'     => $t,
		'hm'    => $hm,
		'est'   => $est,
	];
}

function ptv_payload() {
	$s     = ptv_settings();
	$today = wp_date( 'Y-m-d' );
	$base  = time();
	$days  = [];
	foreach ( [ -1, 0, 1, 2 ] as $i ) {
		$d = ptv_day( wp_date( 'Y-m-d', $base + $i * DAY_IN_SECONDS ) );
		if ( $d ) $days[] = $d;
	}
	$items = [];
	foreach ( (array) $s['items'] as $r ) if ( ptv_live( $r, $today ) && trim( $r['t'] ?? '' ) !== '' ) $items[] = [ 't' => $r['t'], 'p' => ! empty( $r['p'] ) ];
	if ( ! $items && trim( $s['lalai'] ) !== '' ) $items[] = [ 't' => $s['lalai'], 'p' => false ];
	$slides = [];
	foreach ( (array) $s['slides'] as $r ) if ( ptv_live( $r, $today ) && ( trim( $r['h'] ?? '' ) . trim( $r['b'] ?? '' ) ) !== '' ) $slides[] = [ 'h' => $r['h'], 'b' => $r['b'] ];

	$cfg = [
		'nama'   => $s['nama'], 'lokasi' => $s['lokasi'], 'logo' => $s['logo'] ?: PTV_URL . 'assets/logo-masjid.svg',
		'speed'  => (int) $s['speed'], 'label' => $s['label'], 'items' => $items,
		'slides' => $slides, 'slide_sec' => (int) $s['slide_sec'],
		'infaq'  => [ 'mode' => ( '' === trim( $s['akaun'] ) && '' === trim( $s['qr'] ) ) ? 'tiada' : $s['infaq_mode'], 'tajuk' => $s['infaq_tajuk'], 'bank' => $s['bank'], 'akaun' => $s['akaun'], 'nama' => $s['nama_akaun'], 'qr' => $s['qr'], 'nota' => $s['infaq_nota'] ],
		'azan'   => (int) $s['azan_on'], 'iqamah' => array_map( 'intval', $s['iqamah'] ),
		'solat_min' => (int) $s['solat_min'], 'jumaat_min' => (int) $s['jumaat_min'], 'h12' => (int) $s['masa_12j'],
	];
	return [
		'now'  => (int) round( microtime( true ) * 1000 ),
		'tzo'  => wp_timezone()->getOffset( new DateTime( 'now' ) ) * 1000,
		'code' => PTV_VER,
		'ver'  => substr( md5( wp_json_encode( $cfg ) . $today ), 0, 12 ),
		'days' => $days,
		'cfg'  => $cfg,
	];
}
