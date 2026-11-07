<?php
/**
 * Waktu solat — JAKIM e-Solat for the chosen zone, fallback api.waktusolat.app (JAKIM data mirror).
 *  - Each month is stored permanently in wp_options (ptv_ws_{ZON}_{YYYYMM}), so a JAKIM outage never blanks the TV.
 *  - WP-Cron refreshes twice a day (and pre-fetches next month from the 20th).
 *  - If a month is missing and both sources fail: 15-minute back-off, then the latest stored day is shown as an estimate.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/** JAKIM zones (code => areas). Check yours at https://www.e-solat.gov.my/ */
function ptv_zones() {
	return [
		'Johor'           => [ 'JHR01' => 'Pulau Aur, Pulau Pemanggil', 'JHR02' => 'Johor Bahru, Kota Tinggi, Mersing, Kulai', 'JHR03' => 'Kluang, Pontian', 'JHR04' => 'Batu Pahat, Muar, Segamat, Gemas, Tangkak' ],
		'Kedah'           => [ 'KDH01' => 'Kota Setar, Kubang Pasu, Pokok Sena', 'KDH02' => 'Kuala Muda, Yan, Pendang', 'KDH03' => 'Padang Terap, Sik', 'KDH04' => 'Baling', 'KDH05' => 'Bandar Baharu, Kulim', 'KDH06' => 'Langkawi', 'KDH07' => 'Puncak Gunung Jerai' ],
		'Kelantan'        => [ 'KTN01' => 'Bachok, Kota Bharu, Machang, Pasir Mas, Pasir Puteh, Tanah Merah, Tumpat, Kuala Krai, Mukim Chiku', 'KTN02' => 'Gua Musang (Galas & Bertam), Jeli, Lojing' ],
		'Melaka'          => [ 'MLK01' => 'Seluruh Negeri Melaka' ],
		'Negeri Sembilan' => [ 'NGS01' => 'Tampin, Jempol', 'NGS02' => 'Jelebu, Kuala Pilah, Rembau', 'NGS03' => 'Port Dickson, Seremban' ],
		'Pahang'          => [ 'PHG01' => 'Pulau Tioman', 'PHG02' => 'Kuantan, Pekan, Muadzam Shah', 'PHG03' => 'Jerantut, Temerloh, Maran, Bera, Chenor, Jengka', 'PHG04' => 'Bentong, Lipis, Raub', 'PHG05' => 'Genting Sempah, Janda Baik, Bukit Tinggi', 'PHG06' => 'Cameron Highlands, Genting Highlands, Bukit Fraser', 'PHG07' => 'Rompin (Mukim Rompin, Endau, Pontian)' ],
		'Perlis'          => [ 'PLS01' => 'Kangar, Padang Besar, Arau' ],
		'Pulau Pinang'    => [ 'PNG01' => 'Seluruh Negeri Pulau Pinang' ],
		'Perak'           => [ 'PRK01' => 'Tapah, Slim River, Tanjung Malim', 'PRK02' => 'Kuala Kangsar, Sg. Siput, Ipoh, Batu Gajah, Kampar', 'PRK03' => 'Lenggong, Pengkalan Hulu, Grik', 'PRK04' => 'Temengor, Belum', 'PRK05' => 'Kg Gajah, Teluk Intan, Bagan Datuk, Seri Iskandar, Beruas, Parit, Lumut, Sitiawan, Pulau Pangkor', 'PRK06' => 'Selama, Taiping, Bagan Serai, Parit Buntar', 'PRK07' => 'Bukit Larut' ],
		'Sabah'           => [ 'SBH01' => 'Sandakan (Timur), Bukit Garam, Semawang, Temanggong, Tambisan, Sukau', 'SBH02' => 'Beluran, Telupid, Pinangah, Terusan, Kuamut, Sandakan (Barat)', 'SBH03' => 'Lahad Datu, Silabukan, Kunak, Sahabat, Semporna, Tungku, Tawau (Timur)', 'SBH04' => 'Bandar Tawau, Balong, Merotai, Kalabakan, Tawau (Barat)', 'SBH05' => 'Kudat, Kota Marudu, Pitas, Pulau Banggi', 'SBH06' => 'Gunung Kinabalu', 'SBH07' => 'Kota Kinabalu, Ranau, Kota Belud, Tuaran, Penampang, Papar, Putatan', 'SBH08' => 'Pensiangan, Keningau, Tambunan, Nabawan', 'SBH09' => 'Beaufort, Kuala Penyu, Sipitang, Tenom, Long Pasia, Membakut, Weston' ],
		'Sarawak'         => [ 'SWK01' => 'Limbang, Lawas, Sundar, Trusan', 'SWK02' => 'Miri, Niah, Bekenu, Sibuti, Marudi', 'SWK03' => 'Pandan, Belaga, Suai, Tatau, Sebauh, Bintulu', 'SWK04' => 'Sibu, Mukah, Dalat, Song, Igan, Oya, Balingian, Kanowit, Kapit', 'SWK05' => 'Sarikei, Matu, Julau, Rajang, Daro, Bintangor, Belawai', 'SWK06' => 'Lubok Antu, Sri Aman, Roban, Debak, Kabong, Lingga, Engkelili, Betong, Spaoh, Pusa, Saratok', 'SWK07' => 'Serian, Simunjan, Samarahan, Sebuyau, Meludam', 'SWK08' => 'Kuching, Bau, Lundu, Sematan', 'SWK09' => 'Zon Khas (Kampung Patarikan)' ],
		'Selangor'        => [ 'SGR01' => 'Gombak, Petaling, Sepang, Hulu Langat, Hulu Selangor, Shah Alam', 'SGR02' => 'Kuala Selangor, Sabak Bernam', 'SGR03' => 'Klang, Kuala Langat' ],
		'Terengganu'      => [ 'TRG01' => 'Kuala Terengganu, Marang, Kuala Nerus', 'TRG02' => 'Besut, Setiu', 'TRG03' => 'Hulu Terengganu', 'TRG04' => 'Dungun, Kemaman' ],
		'Wilayah Persekutuan' => [ 'WLY01' => 'Kuala Lumpur, Putrajaya', 'WLY02' => 'Labuan' ],
	];
}
function ptv_zone() {
	$z = strtoupper( (string) ( ptv_settings()['zon'] ?? '' ) );
	return preg_match( '/^[A-Z]{3}\d{2}$/', $z ) ? $z : 'WLY01';
}
function ptv_zone_label( $z = null ) {
	$z = $z ?: ptv_zone();
	foreach ( ptv_zones() as $neg => $list ) { if ( isset( $list[ $z ] ) ) { return $z . ' — ' . $list[ $z ] . ' (' . $neg . ')'; } }
	return $z;
}

function ptv_hijri_label( $h ) {
	$bulan = [ 1 => 'Muharram', 'Safar', 'Rabiulawal', 'Rabiulakhir', 'Jamadilawal', 'Jamadilakhir', 'Rejab', 'Syaaban', 'Ramadan', 'Syawal', 'Zulkaedah', 'Zulhijjah' ];
	if ( ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', (string) $h, $m ) ) return '';
	return intval( $m[3] ) . ' ' . $bulan[ intval( $m[2] ) ] . ' ' . $m[1] . 'H';
}

function ptv_ws_key( $ym, $zone = null ) { return 'ptv_ws_' . ( $zone ?: ptv_zone() ) . '_' . str_replace( '-', '', $ym ); }

/** e-Solat dates look like "01-Okt-2026" (Malay month abbreviations) — strtotime() can't read Mac/Mei/Ogo/Okt/Dis. */
function ptv_ws_parse_date( $s ) {
	static $m = [ 'jan' => 1, 'feb' => 2, 'mac' => 3, 'mar' => 3, 'apr' => 4, 'mei' => 5, 'may' => 5, 'jun' => 6, 'jul' => 7, 'ogo' => 8, 'ogs' => 8, 'aug' => 8,
		'sep' => 9, 'okt' => 10, 'oct' => 10, 'nov' => 11, 'dis' => 12, 'dec' => 12 ];
	if ( preg_match( '/^(\d{1,2})-([A-Za-z]{3})[a-z]*-(\d{4})$/', trim( (string) $s ), $x ) && isset( $m[ strtolower( $x[2] ) ] ) ) {
		return sprintf( '%04d-%02d-%02d', $x[3], $m[ strtolower( $x[2] ) ], $x[1] );
	}
	$ts = strtotime( (string) $s );
	return $ts ? gmdate( 'Y-m-d', $ts ) : '';
}

/** Download one month (Y-m) for the zone. Returns [ rows, source ] or [ [], error ]. */
function ptv_ws_download( $ym, $zone = null ) {
	$zone = $zone ?: ptv_zone();
	[ $y, $m ] = array_map( 'intval', explode( '-', $ym ) );
	$first  = sprintf( '%04d-%02d-01', $y, $m );
	$last   = gmdate( 'Y-m-t', strtotime( $first ) );
	$errors = [];
	$hm     = fn( $t ) => substr( (string) $t, 0, 5 );

	// 1) JAKIM e-Solat — current month via period=month, other months via period=duration
	$base = 'https://www.e-solat.gov.my/index.php?r=esolatApi/takwimsolat&zone=' . rawurlencode( $zone );
	$r    = ( $ym === wp_date( 'Y-m' ) )
		? wp_remote_get( $base . '&period=month', [ 'timeout' => 15 ] )
		: wp_remote_post( $base . '&period=duration', [ 'timeout' => 15, 'body' => [ 'datestart' => $first, 'dateend' => $last ] ] );
	if ( is_wp_error( $r ) ) { $errors[] = 'e-Solat: ' . $r->get_error_message(); }
	elseif ( 200 !== wp_remote_retrieve_response_code( $r ) ) { $errors[] = 'e-Solat: HTTP ' . wp_remote_retrieve_response_code( $r ); }
	else {
		$j   = json_decode( wp_remote_retrieve_body( $r ), true );
		$out = [];
		foreach ( (array) ( $j['prayerTime'] ?? [] ) as $d ) {
			$dt = ptv_ws_parse_date( $d['date'] ?? '' );
			if ( $dt && substr( $dt, 0, 7 ) === $ym ) {
				$out[ $dt ] = [ 'hijri' => $d['hijri'] ?? '', 'imsak' => $hm( $d['imsak'] ?? '' ), 'subuh' => $hm( $d['fajr'] ?? '' ), 'syuruk' => $hm( $d['syuruk'] ?? '' ),
					'zohor' => $hm( $d['dhuhr'] ?? '' ), 'asar' => $hm( $d['asr'] ?? '' ), 'maghrib' => $hm( $d['maghrib'] ?? '' ), 'isyak' => $hm( $d['isha'] ?? '' ) ];
			}
		}
		if ( count( $out ) >= 28 ) { return [ $out, 'e-Solat JAKIM' ]; }
		$errors[] = 'e-Solat: ' . count( $out ) . ' hari sahaja';
	}

	// 2) Fallback: api.waktusolat.app (JAKIM data, independent mirror)
	$r = wp_remote_get( 'https://api.waktusolat.app/v2/solat/' . rawurlencode( $zone ) . '?year=' . $y . '&month=' . $m, [ 'timeout' => 15 ] );
	if ( is_wp_error( $r ) ) { $errors[] = 'waktusolat.app: ' . $r->get_error_message(); }
	elseif ( 200 !== wp_remote_retrieve_response_code( $r ) ) { $errors[] = 'waktusolat.app: HTTP ' . wp_remote_retrieve_response_code( $r ); }
	else {
		$j   = json_decode( wp_remote_retrieve_body( $r ), true );
		$tz  = wp_timezone();
		$out = [];
		$f   = fn( $t ) => $t ? ( new DateTime( '@' . $t ) )->setTimezone( $tz )->format( 'H:i' ) : '';
		foreach ( (array) ( $j['prayers'] ?? [] ) as $d ) {
			$date = sprintf( '%04d-%02d-%02d', $j['year'] ?? $y, $j['month_number'] ?? $m, $d['day'] ?? 0 );
			if ( substr( $date, 0, 7 ) !== $ym ) { continue; }
			$out[ $date ] = [ 'hijri' => $d['hijri'] ?? '', 'imsak' => $f( $d['imsak'] ?? 0 ), 'subuh' => $f( $d['fajr'] ?? 0 ), 'syuruk' => $f( $d['syuruk'] ?? 0 ),
				'zohor' => $f( $d['dhuhr'] ?? 0 ), 'asar' => $f( $d['asr'] ?? 0 ), 'maghrib' => $f( $d['maghrib'] ?? 0 ), 'isyak' => $f( $d['isha'] ?? 0 ) ];
		}
		if ( count( $out ) >= 28 ) { return [ $out, 'waktusolat.app (JAKIM)' ]; }
		$errors[] = 'waktusolat.app: ' . count( $out ) . ' hari sahaja';
	}
	return [ [], implode( ' | ', $errors ) ];
}

/** Fetch + store a month. Only overwrites stored data on success. */
function ptv_ws_refresh( $ym ) {
	$zone = ptv_zone();
	[ $rows, $src ] = ptv_ws_download( $ym, $zone );
	$meta = get_option( 'ptv_ws_meta', [] );
	$meta['zone'] = $zone;
	$meta['last_attempt'] = time();
	if ( $rows ) {
		update_option( ptv_ws_key( $ym, $zone ), $rows, false );
		$meta['months'][ $zone . ' ' . $ym ] = [ 'source' => $src, 'fetched' => time(), 'days' => count( $rows ) ];
		$meta['last_ok']    = time();
		$meta['last_error'] = '';
		delete_transient( 'ptv_ws_fail_' . $zone . str_replace( '-', '', $ym ) );
	} else {
		$meta['last_error'] = $src;
		set_transient( 'ptv_ws_fail_' . $zone . str_replace( '-', '', $ym ), 1, 15 * MINUTE_IN_SECONDS );
	}
	$meta['months'] = array_slice( (array) ( $meta['months'] ?? [] ), -8, null, true );
	update_option( 'ptv_ws_meta', $meta, false );
	return (bool) $rows;
}

/** Stored month; fetched on demand only if never stored (and not in a 15-min back-off). */
function ptv_waktu_bulan( $ym = null ) {
	$ym   = $ym ?: wp_date( 'Y-m' );
	$zone = ptv_zone();
	static $mem = [];
	if ( isset( $mem[ $zone . $ym ] ) ) { return $mem[ $zone . $ym ]; }
	$rows = get_option( ptv_ws_key( $ym, $zone ) );
	if ( ! $rows && ! get_transient( 'ptv_ws_fail_' . $zone . str_replace( '-', '', $ym ) ) && ptv_ws_refresh( $ym ) ) { $rows = get_option( ptv_ws_key( $ym, $zone ) ); }
	return $mem[ $zone . $ym ] = is_array( $rows ) ? $rows : [];
}
function ptv_waktu_tarikh( $date ) {
	$rows = ptv_waktu_bulan( substr( $date, 0, 7 ) );
	return $rows[ $date ] ?? null;
}
/** Today's row; if unavailable, the latest stored day marked as an estimate. */
function ptv_waktu_hari_ini() {
	$t = ptv_waktu_tarikh( wp_date( 'Y-m-d' ) );
	if ( $t ) { return $t; }
	global $wpdb;
	$opt  = $wpdb->get_var( $wpdb->prepare( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s ORDER BY option_name DESC LIMIT 1", $wpdb->esc_like( 'ptv_ws_' . ptv_zone() . '_' ) . '%' ) );
	$rows = $opt ? get_option( $opt ) : [];
	if ( ! $rows ) { return null; }
	ksort( $rows );
	return end( $rows ) + [ 'estimate' => true ];
}

/* ---------- background refresh ---------- */
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'ptv_ws_cron' ) ) { wp_schedule_event( time() + 60, 'twicedaily', 'ptv_ws_cron' ); }
} );
add_action( 'ptv_ws_cron', function () {
	ptv_ws_refresh( wp_date( 'Y-m' ) );
	if ( (int) wp_date( 'j' ) >= 20 ) { ptv_ws_refresh( wp_date( 'Y-m', strtotime( wp_date( 'Y-m-01' ) . ' +1 month' ) ) ); }
	// housekeeping: keep ~6 months of every zone
	global $wpdb;
	$cut = wp_date( 'Ym', strtotime( '-6 months' ) );
	foreach ( $wpdb->get_col( $wpdb->prepare( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s", $wpdb->esc_like( 'ptv_ws_' ) . '%' ) ) as $o ) {
		if ( preg_match( '/^ptv_ws_[A-Z]{3}\d{2}_(\d{6})$/', $o, $x ) && $x[1] < $cut ) { delete_option( $o ); }
	}
} );
add_action( 'admin_post_ptv_ws_refresh', function () {
	check_admin_referer( 'ptv_ws_refresh' );
	if ( ! current_user_can( PTV_CAP ) ) { wp_die( 'Tiada kebenaran.' ); }
	$ok = ptv_ws_refresh( wp_date( 'Y-m' ) );
	if ( (int) wp_date( 'j' ) >= 20 ) { ptv_ws_refresh( wp_date( 'Y-m', strtotime( wp_date( 'Y-m-01' ) . ' +1 month' ) ) ); }
	wp_safe_redirect( admin_url( 'admin.php?page=paparan-tv&ptv=' . ( $ok ? 'ws_ok' : 'ws_fail' ) ) );
	exit;
} );
