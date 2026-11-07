<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', function () {
	add_menu_page( 'Paparan TV', 'Paparan TV', PTV_CAP, 'paparan-tv', 'ptv_admin_page', 'dashicons-desktop', 27 );
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( $hook === 'toplevel_page_paparan-tv' ) wp_enqueue_media();
} );

function ptv_date_or_empty( $v ) { $v = sanitize_text_field( (string) $v ); return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $v ) ? $v : ''; }
function ptv_url_or_empty( $v ) { $v = esc_url_raw( trim( (string) $v ) ); return $v; }

add_action( 'admin_post_ptv_save', function () {
	if ( ! current_user_can( PTV_CAP ) ) wp_die( 'Tiada kebenaran.' );
	check_admin_referer( 'ptv_save' );
	$p = wp_unslash( $_POST );
	$d = ptv_defaults();
	$clip = fn( $v, $n ) => mb_substr( sanitize_text_field( (string) $v ), 0, $n );
	$int  = fn( $v, $lo, $hi, $def ) => is_numeric( $v ) ? max( $lo, min( $hi, (int) $v ) ) : $def;

	$items = [];
	foreach ( (array) ( $p['items'] ?? [] ) as $r ) {
		$t = $clip( $r['t'] ?? '', 400 );
		if ( $t === '' ) continue;
		$items[] = [ 't' => $t, 'mula' => ptv_date_or_empty( $r['mula'] ?? '' ), 'tamat' => ptv_date_or_empty( $r['tamat'] ?? '' ), 'on' => empty( $r['on'] ) ? 0 : 1, 'p' => empty( $r['p'] ) ? 0 : 1 ];
		if ( count( $items ) >= 40 ) break;
	}
	$slides = [];
	foreach ( (array) ( $p['slides'] ?? [] ) as $r ) {
		$h = $clip( $r['h'] ?? '', 80 );
		$b = mb_substr( sanitize_textarea_field( (string) ( $r['b'] ?? '' ) ), 0, 400 );
		if ( $h === '' && $b === '' ) continue;
		$slides[] = [ 'h' => $h, 'b' => $b, 'mula' => ptv_date_or_empty( $r['mula'] ?? '' ), 'tamat' => ptv_date_or_empty( $r['tamat'] ?? '' ), 'on' => empty( $r['on'] ) ? 0 : 1 ];
		if ( count( $slides ) >= 20 ) break;
	}
	$iq = [];
	foreach ( PTV_WAJIB as $k ) $iq[ $k ] = $int( $p['iqamah'][ $k ] ?? '', 0, 60, $d['iqamah'][ $k ] );

	$s = [
		'nama'        => $clip( $p['nama'] ?? '', 80 ) ?: $d['nama'],
		'lokasi'      => $clip( $p['lokasi'] ?? '', 120 ),
		'zon'         => array_key_exists( strtoupper( (string) ( $p['zon'] ?? '' ) ), array_merge( ...array_values( ptv_zones() ) ) ) ? strtoupper( $p['zon'] ) : $d['zon'],
		'logo'        => ptv_url_or_empty( $p['logo'] ?? '' ),
		'speed'       => $int( $p['speed'] ?? '', 20, 400, $d['speed'] ),
		'label'       => $clip( $p['label'] ?? '', 30 ),
		'lalai'       => $clip( $p['lalai'] ?? '', 400 ),
		'items'       => $items,
		'slides'      => $slides,
		'slide_sec'   => $int( $p['slide_sec'] ?? '', 5, 300, $d['slide_sec'] ),
		'infaq_mode'  => in_array( $p['infaq_mode'] ?? '', [ 'slaid', 'tetap', 'tiada' ], true ) ? $p['infaq_mode'] : 'slaid',
		'infaq_tajuk' => $clip( $p['infaq_tajuk'] ?? '', 60 ),
		'bank'        => $clip( $p['bank'] ?? '', 60 ),
		'akaun'       => $clip( $p['akaun'] ?? '', 40 ),
		'nama_akaun'  => $clip( $p['nama_akaun'] ?? '', 80 ),
		'qr'          => ptv_url_or_empty( $p['qr'] ?? '' ),
		'infaq_nota'  => $clip( $p['infaq_nota'] ?? '', 160 ),
		'azan_on'     => empty( $p['azan_on'] ) ? 0 : 1,
		'iqamah'      => $iq,
		'solat_min'   => $int( $p['solat_min'] ?? '', 0, 60, $d['solat_min'] ),
		'jumaat_min'  => $int( $p['jumaat_min'] ?? '', 0, 120, $d['jumaat_min'] ),
		'masa_12j'    => ( $p['masa_12j'] ?? '1' ) === '0' ? 0 : 1,
	];
	$zone_changed = ( ptv_settings()['zon'] ?? '' ) !== $s['zon'];
	update_option( PTV_OPT, $s, false );
	if ( $zone_changed ) { ptv_ws_refresh( wp_date( 'Y-m' ) ); }
	wp_safe_redirect( admin_url( 'admin.php?page=paparan-tv&ptv=ok' ) );
	exit;
} );

function ptv_admin_page() {
	if ( ! current_user_can( PTV_CAP ) ) return;
	$s   = ptv_settings();
	$url = home_url( '/' . PTV_SLUG . '/' );
	$e   = fn( $v ) => esc_attr( (string) $v );
	$item_row = function ( $i, $r ) use ( $e ) {
		return '<tr class="ptv-row">'
			. '<td class="ptv-drag" title="Seret untuk susun">⋮⋮</td>'
			. '<td><input type="text" class="large-text" name="items[' . $i . '][t]" value="' . $e( $r['t'] ?? '' ) . '" maxlength="400" placeholder="Teks pengumuman…"></td>'
			. '<td><input type="date" name="items[' . $i . '][mula]" value="' . $e( $r['mula'] ?? '' ) . '"></td>'
			. '<td><input type="date" name="items[' . $i . '][tamat]" value="' . $e( $r['tamat'] ?? '' ) . '"></td>'
			. '<td class="c"><input type="checkbox" name="items[' . $i . '][p]" value="1"' . checked( ! empty( $r['p'] ), true, false ) . '></td>'
			. '<td class="c"><input type="checkbox" name="items[' . $i . '][on]" value="1"' . checked( ! isset( $r['on'] ) || ! empty( $r['on'] ), true, false ) . '></td>'
			. '<td><button type="button" class="button-link-delete ptv-del" title="Padam">✕</button></td></tr>';
	};
	$slide_row = function ( $i, $r ) use ( $e ) {
		return '<tr class="ptv-row">'
			. '<td class="ptv-drag" title="Seret untuk susun">⋮⋮</td>'
			. '<td><input type="text" class="large-text" name="slides[' . $i . '][h]" value="' . $e( $r['h'] ?? '' ) . '" maxlength="80" placeholder="Tajuk"><textarea class="large-text" rows="2" name="slides[' . $i . '][b]" maxlength="400" placeholder="Mesej">' . esc_textarea( $r['b'] ?? '' ) . '</textarea></td>'
			. '<td><input type="date" name="slides[' . $i . '][mula]" value="' . $e( $r['mula'] ?? '' ) . '"></td>'
			. '<td><input type="date" name="slides[' . $i . '][tamat]" value="' . $e( $r['tamat'] ?? '' ) . '"></td>'
			. '<td class="c"><input type="checkbox" name="slides[' . $i . '][on]" value="1"' . checked( ! isset( $r['on'] ) || ! empty( $r['on'] ), true, false ) . '></td>'
			. '<td><button type="button" class="button-link-delete ptv-del" title="Padam">✕</button></td></tr>';
	};
	?>
	<div class="wrap ptv-admin">
		<h1 class="wp-heading-inline">Paparan TV</h1>
		<a class="page-title-action" href="<?php echo esc_url( $url ); ?>" target="_blank">Buka paparan ↗</a>
		<hr class="wp-header-end">
		<?php if ( ( $_GET['ptv'] ?? '' ) === 'ok' ) echo '<div class="notice notice-success is-dismissible"><p>Tetapan disimpan. TV akan mengemas kini dalam masa 1 minit.</p></div>'; ?>
		<?php if ( ( $_GET['ptv'] ?? '' ) === 'ws_ok' ) echo '<div class="notice notice-success is-dismissible"><p>Waktu solat dikemas kini daripada JAKIM.</p></div>'; ?>
		<?php if ( ( $_GET['ptv'] ?? '' ) === 'ws_fail' ) echo '<div class="notice notice-error is-dismissible"><p>Gagal mendapatkan waktu solat — data sedia ada dikekalkan. Cuba lagi sebentar.</p></div>'; ?>
		<?php
		$meta  = get_option( 'ptv_ws_meta', [] );
		$today = ptv_waktu_tarikh( wp_date( 'Y-m-d' ) );
		$fmt   = fn( $t ) => $t ? wp_date( 'j M Y, g:i a', $t ) : '—';
		echo '<div class="ptv-card ptv-ws"><h2>Waktu solat — zon ' . esc_html( ptv_zone_label() ) . '</h2>';
		if ( $today ) {
			echo '<p class="ptv-ws-today">';
			foreach ( PTV_PRAYERS as $k ) { echo '<span><small>' . esc_html( ucfirst( $k ) ) . '</small><b>' . esc_html( $today[ $k ] ?? '—' ) . '</b></span>'; }
			echo '</p>';
		} else {
			echo '<p style="color:#b32d2e"><strong>Waktu solat hari ini belum dapat dimuatkan.</strong> Pastikan pelayan boleh menghubungi e-solat.gov.my, kemudian klik "Muat semula".</p>';
		}
		echo '<p class="description">Kemas kini terakhir berjaya: ' . esc_html( $fmt( $meta['last_ok'] ?? 0 ) ) . ' · seterusnya: ' . esc_html( $fmt( wp_next_scheduled( 'ptv_ws_cron' ) ) ) . ( ! empty( $meta['last_error'] ) ? ' · <span style="color:#b32d2e">ralat terakhir: ' . esc_html( $meta['last_error'] ) . '</span>' : '' ) . '</p>';
		echo '<p><a class="button" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ptv_ws_refresh' ), 'ptv_ws_refresh' ) ) . '">Muat semula waktu solat</a></p></div>';
		?>

		<div class="ptv-url">
			<span class="dashicons dashicons-desktop"></span>
			<div><strong>Alamat untuk Android TV:</strong> <code id="ptv-link"><?php echo esc_html( $url ); ?></code>
			<button type="button" class="button button-small" onclick="navigator.clipboard&&navigator.clipboard.writeText(document.getElementById('ptv-link').textContent);this.textContent='Disalin ✓'">Salin</button>
			<br><span class="description">Buka alamat ini dalam pelayar TV (cth. Fully Kiosk Browser / TV Bro) dalam mod skrin penuh. Waktu solat diambil daripada JAKIM e-Solat untuk zon <strong><?php echo esc_html( ptv_zone() ); ?></strong> (tetapan 5). Perubahan di sini dipaparkan di TV secara automatik dalam ±1 minit — tak perlu sentuh TV.</span></div>
		</div>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="ptv_save">
			<?php wp_nonce_field( 'ptv_save' ); ?>

			<div class="ptv-card">
				<h2>1. Pengumuman bergerak (marquee)</h2>
				<table class="form-table" role="presentation">
					<tr><th>Kelajuan</th><td>
						<input type="range" id="ptv-speed" name="speed" min="20" max="400" step="5" value="<?php echo (int) $s['speed']; ?>" style="width:320px;vertical-align:middle">
						<output id="ptv-speed-out" style="font-weight:600;margin-left:8px"><?php echo (int) $s['speed']; ?></output> piksel/saat
						<span class="description" id="ptv-speed-hint"></span>
						<div class="ptv-preview"><span class="ptv-pv-label" id="ptv-pv-label"><?php echo esc_html( $s['label'] ); ?></span><div class="ptv-pv-win"><div class="ptv-pv-track" id="ptv-pv-track"></div></div></div>
						<p class="description">Pratonton di atas bergerak pada kelajuan sebenar (diskalakan mengikut lebar skrin TV 1920px). 60–120 sesuai untuk dibaca oleh jemaah.</p>
					</td></tr>
					<tr><th><label for="ptv-label">Label</label></th><td><input type="text" id="ptv-label" name="label" value="<?php echo esc_attr( $s['label'] ); ?>" maxlength="30" class="regular-text"></td></tr>
					<tr><th><label>Teks lalai</label></th><td><input type="text" name="lalai" value="<?php echo esc_attr( $s['lalai'] ); ?>" maxlength="400" class="large-text"><p class="description">Dipaparkan jika tiada pengumuman aktif.</p></td></tr>
				</table>
				<table class="widefat ptv-table" id="ptv-items">
					<thead><tr><th style="width:24px"></th><th>Pengumuman</th><th style="width:140px">Mula</th><th style="width:140px">Tamat</th><th class="c" style="width:70px">Penting</th><th class="c" style="width:60px">Aktif</th><th style="width:30px"></th></tr></thead>
					<tbody><?php foreach ( array_values( (array) $s['items'] ) as $i => $r ) echo $item_row( $i, $r ); // phpcs:ignore ?></tbody>
				</table>
				<p><button type="button" class="button" data-add="ptv-items">+ Tambah pengumuman</button>
				<span class="description">Kosongkan Mula/Tamat untuk paparan berterusan. "Penting" = diserlahkan warna emas.</span></p>
			</div>

			<div class="ptv-card">
				<h2>2. Slaid mesej (panel tengah)</h2>
				<table class="widefat ptv-table" id="ptv-slides">
					<thead><tr><th style="width:24px"></th><th>Tajuk &amp; mesej</th><th style="width:140px">Mula</th><th style="width:140px">Tamat</th><th class="c" style="width:60px">Aktif</th><th style="width:30px"></th></tr></thead>
					<tbody><?php foreach ( array_values( (array) $s['slides'] ) as $i => $r ) echo $slide_row( $i, $r ); // phpcs:ignore ?></tbody>
				</table>
				<p><button type="button" class="button" data-add="ptv-slides">+ Tambah slaid</button></p>
				<table class="form-table" role="presentation"><tr><th>Tempoh setiap slaid</th><td><input type="number" name="slide_sec" min="5" max="300" value="<?php echo (int) $s['slide_sec']; ?>" class="small-text"> saat</td></tr></table>
			</div>

			<div class="ptv-card">
				<h2>3. Infaq — DuitNow QR &amp; akaun bank</h2>
				<table class="form-table" role="presentation">
					<tr><th>Paparan</th><td>
						<label><input type="radio" name="infaq_mode" value="slaid" <?php checked( $s['infaq_mode'], 'slaid' ); ?>> Dalam putaran slaid</label><br>
						<label><input type="radio" name="infaq_mode" value="tetap" <?php checked( $s['infaq_mode'], 'tetap' ); ?>> Sentiasa dipaparkan (sebelah slaid)</label><br>
						<label><input type="radio" name="infaq_mode" value="tiada" <?php checked( $s['infaq_mode'], 'tiada' ); ?>> Tidak dipaparkan</label>
					</td></tr>
					<tr><th>Tajuk</th><td><input type="text" name="infaq_tajuk" value="<?php echo esc_attr( $s['infaq_tajuk'] ); ?>" class="regular-text" maxlength="60"></td></tr>
					<tr><th>Bank</th><td><input type="text" name="bank" value="<?php echo esc_attr( $s['bank'] ); ?>" class="regular-text" maxlength="60"></td></tr>
					<tr><th>No. akaun</th><td><input type="text" name="akaun" value="<?php echo esc_attr( $s['akaun'] ); ?>" class="regular-text" maxlength="40"></td></tr>
					<tr><th>Nama akaun</th><td><input type="text" name="nama_akaun" value="<?php echo esc_attr( $s['nama_akaun'] ); ?>" class="regular-text" maxlength="80"></td></tr>
					<tr><th>Kod QR</th><td>
						<img id="ptv-qr-img" src="<?php echo esc_url( $s['qr'] ); ?>" alt="" style="width:120px;height:120px;object-fit:contain;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:4px;vertical-align:middle">
						<input type="hidden" id="ptv-qr" name="qr" value="<?php echo esc_attr( $s['qr'] ); ?>">
						<button type="button" class="button" id="ptv-qr-pick">Tukar imej QR…</button>
					</td></tr>
					<tr><th>Nota</th><td><input type="text" name="infaq_nota" value="<?php echo esc_attr( $s['infaq_nota'] ); ?>" class="large-text" maxlength="160"></td></tr>
				</table>
			</div>

			<div class="ptv-card">
				<h2>4. Skrin azan &amp; iqamah</h2>
				<table class="form-table" role="presentation">
					<tr><th>Aktif</th><td><label><input type="checkbox" name="azan_on" value="1" <?php checked( $s['azan_on'], 1 ); ?>> Bila masuk waktu, paparkan "Telah masuk waktu …" dengan kiraan detik iqamah</label></td></tr>
					<tr><th>Iqamah (minit selepas azan)</th><td>
						<?php foreach ( PTV_WAJIB as $k ) : ?>
							<label style="display:inline-block;margin-right:14px"><?php echo esc_html( ucfirst( $k ) ); ?> <input type="number" name="iqamah[<?php echo esc_attr( $k ); ?>]" min="0" max="60" value="<?php echo (int) $s['iqamah'][ $k ]; ?>" class="small-text"></label>
						<?php endforeach; ?>
					</td></tr>
					<tr><th>Skrin "solat sedang didirikan"</th><td><input type="number" name="solat_min" min="0" max="60" value="<?php echo (int) $s['solat_min']; ?>" class="small-text"> minit selepas iqamah</td></tr>
					<tr><th>Jumaat</th><td><input type="number" name="jumaat_min" min="0" max="120" value="<?php echo (int) $s['jumaat_min']; ?>" class="small-text"> minit skrin "Khutbah &amp; Solat Jumaat" (tiada kiraan iqamah)</td></tr>
				</table>
			</div>

			<div class="ptv-card">
				<h2>5. Umum</h2>
				<table class="form-table" role="presentation">
					<tr><th><label for="ptv-zon">Zon waktu solat JAKIM</label></th><td><select id="ptv-zon" name="zon">
						<?php foreach ( ptv_zones() as $neg => $list ) : ?><optgroup label="<?php echo esc_attr( $neg ); ?>"><?php foreach ( $list as $code => $area ) : ?><option value="<?php echo esc_attr( $code ); ?>" <?php selected( $s['zon'], $code ); ?>><?php echo esc_html( $code . ' — ' . $area ); ?></option><?php endforeach; ?></optgroup><?php endforeach; ?>
					</select><p class="description">Semak zon anda di <a href="https://www.e-solat.gov.my/" target="_blank" rel="noopener">e-solat.gov.my</a>. Zona waktu laman web mesti <em>Asia/Kuala_Lumpur</em> (Tetapan → Umum).</p></td></tr>
					<tr><th>Nama masjid</th><td><input type="text" name="nama" value="<?php echo esc_attr( $s['nama'] ); ?>" class="regular-text" maxlength="80"></td></tr>
					<tr><th>Lokasi</th><td><input type="text" name="lokasi" value="<?php echo esc_attr( $s['lokasi'] ); ?>" class="regular-text" maxlength="120"></td></tr>
					<tr><th>Format jam</th><td><select name="masa_12j"><option value="1" <?php selected( $s['masa_12j'], 1 ); ?>>12 jam (4:15 petang)</option><option value="0" <?php selected( $s['masa_12j'], 0 ); ?>>24 jam (16:15)</option></select></td></tr>
					<tr><th>Logo</th><td>
						<span style="display:inline-block;background:#1d3314;border-radius:8px;padding:6px;vertical-align:middle"><img id="ptv-logo-img" src="<?php echo esc_url( $s['logo'] ?: PTV_URL . 'assets/logo-masjid.svg' ); ?>" alt="" style="width:64px;height:64px;object-fit:contain;display:block"></span>
						<input type="hidden" id="ptv-logo" name="logo" value="<?php echo esc_attr( $s['logo'] ); ?>">
						<button type="button" class="button" id="ptv-logo-pick">Tukar logo…</button> <button type="button" class="button-link" id="ptv-logo-clear">Guna ikon lalai</button>
						<p class="description">Logo putih / lutsinar paling sesuai (latar TV berwarna gelap).</p></td></tr>
				</table>
			</div>

			<?php submit_button( 'Simpan tetapan' ); ?>
		</form>

		<template id="tpl-ptv-items"><?php echo $item_row( '__i__', [ 'on' => 1 ] ); // phpcs:ignore ?></template>
		<template id="tpl-ptv-slides"><?php echo $slide_row( '__i__', [ 'on' => 1 ] ); // phpcs:ignore ?></template>
	</div>
	<style>
		.ptv-admin .ptv-card{background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:6px 20px 14px;margin:16px 0;max-width:1100px}
		.ptv-admin .ptv-card h2{color:#2f4d20}
		.ptv-admin .ptv-url{display:flex;gap:12px;align-items:flex-start;background:#eef7e2;border:1px solid #c7e3a3;border-radius:10px;padding:14px 18px;margin:14px 0;max-width:1100px}
		.ptv-admin .ptv-url .dashicons{font-size:28px;width:28px;height:28px;color:#527c3a}
		.ptv-admin .ptv-url code{font-size:14px;background:#fff}
		.ptv-table td,.ptv-table th{vertical-align:middle}.ptv-table .c{text-align:center}
		.ptv-table textarea{margin-top:4px}
		.ptv-ws-today{display:flex;flex-wrap:wrap;gap:8px}.ptv-ws-today span{background:#f6f9f2;border:1px solid #e3eadb;border-radius:8px;padding:6px 12px;text-align:center;min-width:76px}.ptv-ws-today small{display:block;color:#5f6b5a}.ptv-ws-today b{font-size:16px}
		.ptv-drag{cursor:grab;color:#8c8f94;user-select:none}
		.ptv-row.drag{opacity:.4}
		.ptv-preview{display:flex;align-items:stretch;margin-top:10px;width:640px;max-width:100%;height:40px;border-radius:8px;overflow:hidden;background:#1d3314;font:500 16px Poppins,system-ui,sans-serif}
		.ptv-pv-label{background:#8dc642;color:#1d3314;font-weight:700;text-transform:uppercase;padding:0 12px;display:flex;align-items:center;font-size:13px;letter-spacing:.05em;white-space:nowrap}
		.ptv-pv-win{flex:1;overflow:hidden;position:relative}
		.ptv-pv-track{position:absolute;top:0;left:0;height:100%;display:flex;align-items:center;white-space:nowrap;color:#fff;will-change:transform}
		.ptv-pv-track span{padding:0 18px}.ptv-pv-track i{color:#c9a227;font-style:normal}
	</style>
	<script>
	(function(){
		// add / delete rows
		document.querySelectorAll('[data-add]').forEach(function(b){
			b.addEventListener('click',function(){
				var id=b.getAttribute('data-add'),tb=document.querySelector('#'+id+' tbody');
				var html=document.getElementById('tpl-'+id).innerHTML.replace(/__i__/g,'n'+Date.now());
				tb.insertAdjacentHTML('beforeend',html); tb.lastElementChild.querySelector('input,textarea').focus(); drag(tb.lastElementChild); buildPreview();
			});
		});
		document.addEventListener('click',function(e){ if(e.target.classList.contains('ptv-del')){ e.target.closest('tr').remove(); buildPreview(); } });
		// drag to reorder (order of rows = order on TV; PHP re-indexes on save)
		var dragging=null;
		function drag(tr){
			var h=tr.querySelector('.ptv-drag'); if(!h) return;
			h.addEventListener('mousedown',function(){ tr.draggable=true; });
			tr.addEventListener('dragstart',function(){ dragging=tr; tr.classList.add('drag'); });
			tr.addEventListener('dragend',function(){ tr.classList.remove('drag'); tr.draggable=false; dragging=null; buildPreview(); });
			tr.addEventListener('dragover',function(e){ e.preventDefault(); if(!dragging||dragging===tr||dragging.parentNode!==tr.parentNode) return;
				var r=tr.getBoundingClientRect(); tr.parentNode.insertBefore(dragging,(e.clientY-r.top)>r.height/2?tr.nextSibling:tr); });
		}
		document.querySelectorAll('.ptv-row').forEach(drag);

		// live marquee preview at real speed (scaled from a 1920px TV stage)
		var sp=document.getElementById('ptv-speed'),out=document.getElementById('ptv-speed-out'),hint=document.getElementById('ptv-speed-hint');
		var track=document.getElementById('ptv-pv-track'),x=0,last=0,half=0;
		function texts(){ var a=[]; document.querySelectorAll('#ptv-items tbody tr').forEach(function(tr){ var t=tr.querySelector('input[type=text]').value.trim(), on=tr.querySelector('input[name$="[on]"]').checked; if(t&&on) a.push(t); }); if(!a.length){ var l=document.querySelector('input[name=lalai]').value.trim(); if(l) a.push(l);} return a; }
		function buildPreview(){
			var a=texts(),one=a.map(function(t){return '<span>'+t.replace(/[<>&]/g,'')+'</span><i>✦</i>';}).join('');
			track.innerHTML=one+one; half=track.scrollWidth/2; x=0;
		}
		function tick(ts){
			if(!last) last=ts; var dt=(ts-last)/1000; last=ts;
			var scale=track.parentNode.clientWidth/1720; // TV marquee window ≈1720px of 1920
			x-= sp.value*scale*dt; if(half && -x>=half) x+=half;
			track.style.transform='translateX('+x+'px)'; requestAnimationFrame(tick);
		}
		function upd(){ out.textContent=sp.value; var v=+sp.value; hint.textContent=v<60?' — perlahan':v<=120?' — sederhana (disyorkan)':v<=200?' — laju':' — sangat laju'; }
		sp.addEventListener('input',upd); upd();
		document.getElementById('ptv-label').addEventListener('input',function(){ document.getElementById('ptv-pv-label').textContent=this.value; });
		document.getElementById('ptv-items').addEventListener('input',buildPreview);
		document.getElementById('ptv-items').addEventListener('change',buildPreview);
		buildPreview(); requestAnimationFrame(tick);

		// media pickers (QR, logo)
		function picker(btn, input, img, title){
			var fr; document.getElementById(btn).addEventListener('click',function(){
				if(!window.wp||!wp.media) return; if(!fr){ fr=wp.media({title:title,library:{type:'image'},multiple:false});
				fr.on('select',function(){ var a=fr.state().get('selection').first().toJSON(); document.getElementById(input).value=a.url; document.getElementById(img).src=a.url; }); }
				fr.open();
			});
		}
		picker('ptv-qr-pick','ptv-qr','ptv-qr-img','Pilih imej QR');
		picker('ptv-logo-pick','ptv-logo','ptv-logo-img','Pilih logo');
		document.getElementById('ptv-logo-clear').addEventListener('click',function(){ document.getElementById('ptv-logo').value=''; document.getElementById('ptv-logo-img').src=<?php echo wp_json_encode( PTV_URL . 'assets/logo-masjid.svg' ); ?>; });
	})();
	</script>
	<?php
}

add_action( 'admin_notices', function () {
	if ( ! get_transient( 'ptv_welcome' ) || ! current_user_can( PTV_CAP ) ) return;
	delete_transient( 'ptv_welcome' );
	echo '<div class="notice notice-success is-dismissible"><p><strong>Paparan TV Masjid diaktifkan.</strong> Pilih <a href="' . esc_url( admin_url( 'admin.php?page=paparan-tv' ) ) . '">zon waktu solat JAKIM</a> dahulu, kemudian buka <a href="' . esc_url( home_url( '/' . PTV_SLUG . '/' ) ) . '" target="_blank">/' . esc_html( PTV_SLUG ) . '/</a> di pelayar TV.</p></div>';
} );
