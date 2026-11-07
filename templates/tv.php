<?php
/** Standalone full-screen TV page. $ptv_data is provided by the caller. */
if ( ! defined( 'ABSPATH' ) ) exit;
$c = $ptv_data['cfg'];
?><!doctype html>
<html lang="ms">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
<meta name="robots" content="noindex,nofollow">
<meta name="theme-color" content="#1d3314">
<title><?php echo esc_html( $c['nama'] ); ?> — Paparan TV</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{
  --green:#527c3a;--green-dark:#2f4d20;--deep:#1d3314;--deeper:#0f1d0a;--leaf:#8dc642;--leaf-soft:#eef7e2;--gold:#c9a227;--gold-hi:#e7c24a;--ink:#1c1a1b;
  --card:rgba(255,255,255,.055);--line:rgba(141,198,66,.22);--txt:#f4f8ef;--mute:#b9cbaa;
}
*{box-sizing:border-box;margin:0;padding:0}
html,body{width:100%;height:100%;overflow:hidden;background:var(--deeper);color:var(--txt);cursor:none;
  font-family:Poppins,"Segoe UI",Roboto,system-ui,sans-serif;-webkit-font-smoothing:antialiased}
.num{font-variant-numeric:tabular-nums;font-feature-settings:"tnum"}
#stage{position:absolute;left:50%;top:50%;width:1920px;height:1080px;transform-origin:50% 50%;overflow:hidden;
  background:
    radial-gradient(1200px 700px at 18% 30%,rgba(141,198,66,.16),transparent 60%),
    radial-gradient(900px 600px at 90% 85%,rgba(201,162,39,.10),transparent 60%),
    linear-gradient(160deg,#25411a 0%,var(--deep) 45%,var(--deeper) 100%)}
#stage:before{content:"";position:absolute;top:0;left:0;right:0;bottom:0;opacity:.07;pointer-events:none;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='96' height='96' viewBox='0 0 96 96'%3E%3Cg fill='none' stroke='%238dc642' stroke-width='1.6'%3E%3Cpath d='M48 8l11.3 16.4L78 20l-4.4 18.7L90 48l-16.4 9.3L78 76l-18.7-4.4L48 88l-11.3-16.4L18 76l4.4-18.7L6 48l16.4-9.3L18 20l18.7 4.4z'/%3E%3Ccircle cx='48' cy='48' r='14'/%3E%3C/g%3E%3C/svg%3E");
  background-size:96px 96px}
/* header */
.hd{position:absolute;left:56px;right:56px;top:36px;height:120px;display:flex;align-items:center;justify-content:space-between}
.brand{display:flex;align-items:center}
.brand img{height:104px;width:auto;margin-right:26px;filter:drop-shadow(0 4px 14px rgba(0,0,0,.35))}
.brand .n{font-size:50px;font-weight:700;line-height:1.05;letter-spacing:-.01em}
.brand .l{font-size:24px;color:var(--leaf);font-weight:500;margin-top:6px}
.dt{text-align:right}
.dt .g{font-size:38px;font-weight:600;line-height:1.15}
.dt .h{font-size:28px;color:var(--gold-hi);font-weight:500;margin-top:4px}
.dt .est{display:none;margin-top:6px;font-size:18px;color:#ffd27a}
/* main */
.mn{position:absolute;left:56px;right:56px;top:184px;height:560px;display:flex}
.left{width:650px;margin-right:36px;display:flex;flex-direction:column}
.clock{display:flex;align-items:flex-end;line-height:.9}
.clock .hm{font-size:184px;font-weight:700;letter-spacing:-.04em}
.clock .side{margin-left:18px;padding-bottom:22px}
.clock .s{font-size:64px;font-weight:600;color:var(--leaf);display:block;line-height:1}
.clock .p{white-space:nowrap;font-size:28px;font-weight:600;color:var(--mute);text-transform:uppercase;letter-spacing:.08em;display:block;margin-top:8px}
.now{margin-top:22px;font-size:28px;color:var(--mute)}
.now b{color:var(--leaf);font-weight:600}
.nextbox{margin-top:24px;border-radius:26px;padding:24px 34px;background:linear-gradient(135deg,rgba(201,162,39,.22),rgba(201,162,39,.06));border:2px solid rgba(231,194,74,.55)}
.nextbox .t{font-size:30px;font-weight:500;color:#f3e3a6}
.nextbox .t b{font-weight:700;color:#fff}
.nextbox .cd{font-size:112px;font-weight:700;color:var(--gold-hi);line-height:1.05;letter-spacing:-.02em}
.nextbox.soon{animation:pulse 1.2s ease-in-out infinite}
@keyframes pulse{50%{box-shadow:0 0 0 10px rgba(231,194,74,.18);border-color:#ffe08a}}
.right{flex:1;display:flex}
.panel{flex:1;position:relative;border-radius:30px;background:var(--card);border:1.5px solid var(--line);overflow:hidden;
  box-shadow:0 30px 60px rgba(0,0,0,.25),inset 0 1px 0 rgba(255,255,255,.06)}
.slide{position:absolute;top:0;left:0;right:0;bottom:0;padding:56px 64px;display:flex;flex-direction:column;justify-content:center;opacity:0;transition:opacity .9s ease;transform:translateZ(0)}
.slide.on{opacity:1}
.tag{align-self:flex-start;font-size:22px;font-weight:600;color:var(--deep);background:var(--leaf);padding:6px 20px;border-radius:999px;margin-bottom:26px;text-transform:uppercase;letter-spacing:.06em}
.slide h2{font-size:64px;font-weight:700;line-height:1.12;margin-bottom:22px}
.slide p{font-size:38px;line-height:1.45;color:#dfe9d6;white-space:pre-line}
.dots{position:absolute;left:64px;bottom:30px;display:flex}
.dots i{width:14px;height:14px;border-radius:50%;background:rgba(255,255,255,.22);margin-right:12px}
.dots i.on{background:var(--leaf);width:40px;border-radius:8px}
/* infaq */
.infaq{display:flex;align-items:center}
.infaq .qr{background:#fff;border-radius:24px;padding:18px;margin-right:48px;flex:none;box-shadow:0 18px 40px rgba(0,0,0,.35)}
.infaq .qr img{display:block;width:330px;height:330px;object-fit:contain}
.infaq .tag{display:inline-block;margin-bottom:22px}
.infaq .k{font-size:26px;color:var(--mute);margin-top:18px}
.infaq .b{font-size:36px;font-weight:600}
.infaq .a{font-size:62px;font-weight:700;color:var(--gold-hi);letter-spacing:.02em;line-height:1.1}
.infaq .nm{font-size:32px;font-weight:600}
.infaq .nt{font-size:24px;color:var(--mute);margin-top:22px}
.fixinfaq{display:none;width:440px;margin-left:28px;border-radius:30px;background:linear-gradient(180deg,rgba(141,198,66,.16),rgba(141,198,66,.04));border:1.5px solid var(--line);
  padding:30px 34px;flex-direction:column;align-items:center;text-align:center}
.fixinfaq .tag{align-self:center;margin-bottom:18px}
.fixinfaq .qr{background:#fff;border-radius:20px;padding:14px}
.fixinfaq .qr img{display:block;width:250px;height:250px;object-fit:contain}
.fixinfaq .b{font-size:26px;font-weight:600;margin-top:18px}
.fixinfaq .a{font-size:40px;font-weight:700;color:var(--gold-hi);line-height:1.15}
.fixinfaq .nm{font-size:22px;color:var(--mute)}
body.infaq-tetap .fixinfaq{display:flex}
body.infaq-tetap .slide{padding:44px 50px}
body.infaq-tetap .slide h2{font-size:52px}
body.infaq-tetap .slide p{font-size:32px}
body.infaq-tetap .dots{left:50px}
/* tiles */
.tiles{position:absolute;left:56px;right:56px;top:772px;height:176px;display:flex}
.tile{flex:1;margin-right:20px;border-radius:24px;background:var(--card);border:1.5px solid var(--line);position:relative;
  display:flex;flex-direction:column;align-items:center;justify-content:center;transition:all .5s}
.tile:last-child{margin-right:0}
.tile .n{font-size:30px;font-weight:600;color:var(--mute);text-transform:uppercase;letter-spacing:.06em}
.tile .t{font-size:62px;font-weight:700;line-height:1.1}
.tile .x{position:absolute;top:-17px;left:50%;transform:translateX(-50%);font-size:17px;font-weight:700;padding:4px 16px;border-radius:999px;white-space:nowrap;letter-spacing:.08em;display:none}
.tile .c{font-size:22px;font-weight:600;color:var(--gold-hi);display:none}
.tile.minor .n{font-size:24px}.tile.minor .t{font-size:46px;color:#d7e3cc}
.tile.cur{background:linear-gradient(160deg,#a3d65e,var(--leaf) 45%,#6fa82f);border-color:#c6ec8f;transform:translateY(-10px);box-shadow:0 22px 44px rgba(141,198,66,.35)}
.tile.cur .n,.tile.cur .t{color:var(--deep)}
.tile.cur .x{display:block;background:var(--deep);color:var(--leaf);border:2px solid var(--leaf)}
.tile.nxt{border:3px solid var(--gold-hi);background:rgba(201,162,39,.14);transform:translateY(-10px)}
.tile.nxt .n{color:#f3e3a6}
.tile.nxt .x{display:block;background:var(--gold-hi);color:var(--deep)}
.tile.nxt .c{display:block}
/* marquee */
.mq{position:absolute;left:0;right:0;bottom:0;height:96px;display:flex;background:rgba(8,16,5,.72);border-top:2px solid rgba(141,198,66,.4)}
.mq .lb{flex:none;display:flex;align-items:center;padding:0 34px 0 40px;background:var(--leaf);color:var(--deep);font-size:28px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;
  clip-path:polygon(0 0,100% 0,calc(100% - 26px) 100%,0 100%);padding-right:56px}
.mq .win{flex:1;position:relative;overflow:hidden}
.mq .trk{position:absolute;left:0;top:0;height:100%;display:flex;align-items:center;white-space:nowrap;will-change:transform;
  animation-name:mq;animation-timing-function:linear;animation-iteration-count:infinite}
.mq .it{font-size:38px;font-weight:500;padding:0 30px}
.mq .it.p{color:var(--gold-hi);font-weight:700}
.mq .it.p:before{content:"PENTING";font-size:18px;font-weight:800;color:var(--deep);background:var(--gold-hi);padding:3px 12px;border-radius:6px;margin-right:16px;vertical-align:middle;letter-spacing:.06em}
.mq .sp{color:var(--leaf);font-size:28px}
@keyframes mq{from{transform:translate3d(0,0,0)}to{transform:translate3d(-50%,0,0)}}
/* azan / iqamah overlay */
.ov{position:absolute;left:0;right:0;top:172px;bottom:96px;display:none;flex-direction:column;align-items:center;justify-content:center;text-align:center;
  background:radial-gradient(900px 500px at 50% 45%,rgba(141,198,66,.22),transparent 70%),linear-gradient(180deg,rgba(15,29,10,.97),rgba(15,29,10,.99))}
body.ov-on .ov{display:flex}
body.ov-on .mn,body.ov-on .tiles{visibility:hidden}
.ov .k{font-size:44px;color:var(--leaf);font-weight:600;letter-spacing:.04em}
.ov .w{font-size:190px;font-weight:800;line-height:1;margin:10px 0 18px;letter-spacing:-.02em}
.ov .s{font-size:44px;color:#dfe9d6}
.ov .cd{font-size:200px;font-weight:800;color:var(--gold-hi);line-height:1.05;letter-spacing:-.03em}
.ov .m{font-size:34px;color:var(--mute);margin-top:26px}
body.ov-solat .ov{background:#050a03}
body.ov-solat .ov .w{font-size:120px;color:#cfe3bb}
/* status dot */
#net{position:absolute;right:18px;bottom:108px;width:12px;height:12px;border-radius:50%;background:#e25b45;display:none}
body.offline #net{display:block}
</style>
</head>
<body>
<div id="stage">
  <header class="hd">
    <div class="brand"><img id="logo" src="<?php echo esc_url( $c['logo'] ); ?>" alt=""><div><div class="n" id="nama"><?php echo esc_html( $c['nama'] ); ?></div><div class="l" id="lokasi"><?php echo esc_html( $c['lokasi'] ); ?></div></div></div>
    <div class="dt"><div class="g" id="greg">&nbsp;</div><div class="h" id="hijri">&nbsp;</div><div class="est" id="est">⚠ Waktu anggaran — rujuk e-Solat JAKIM</div></div>
  </header>

  <section class="mn">
    <div class="left">
      <div class="clock num"><span class="hm" id="hm">--:--</span><span class="side"><span class="s" id="ss">--</span><span class="p" id="pd"></span></span></div>
      <div class="now" id="nowtxt">&nbsp;</div>
      <div class="nextbox" id="nextbox"><div class="t" id="nexttxt">&nbsp;</div><div class="cd num" id="nextcd">--:--:--</div></div>
    </div>
    <div class="right">
      <div class="panel" id="panel"><div class="dots" id="dots"></div></div>
      <div class="fixinfaq" id="fixinfaq"></div>
    </div>
  </section>

  <section class="tiles" id="tiles"></section>

  <div class="ov" id="ov"><div class="k" id="ovk"></div><div class="w" id="ovw"></div><div class="s" id="ovs"></div><div class="cd num" id="ovcd"></div><div class="m" id="ovm"></div></div>

  <footer class="mq"><div class="lb" id="mqlb"><?php echo esc_html( $c['label'] ); ?></div><div class="win" id="mqwin"><div class="trk" id="mqtrk"></div></div></footer>
  <div id="net" title="Tiada sambungan — memaparkan data tersimpan"></div>
</div>

<script id="ptv-init" type="application/json"><?php echo wp_json_encode( $ptv_data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE ); ?></script>
<script>
(function(){
'use strict';
var API = <?php echo wp_json_encode( esc_url_raw( rest_url( 'paparan-tv/v1/data' ) ) ); ?>;
var D = JSON.parse(document.getElementById('ptv-init').textContent);
var shift = 0, offset = D.now - Date.now(), booted = Date.now(), cfgVer = '', curSlides = [], slideIdx = 0, slideTimer = null;
var $ = function(id){ return document.getElementById(id); };
var HARI = ['Ahad','Isnin','Selasa','Rabu','Khamis','Jumaat','Sabtu'];
var BULAN = ['Januari','Februari','Mac','April','Mei','Jun','Julai','Ogos','September','Oktober','November','Disember'];
var NAMA = {imsak:'Imsak',subuh:'Subuh',syuruk:'Syuruk',zohor:'Zohor',asar:'Asar',maghrib:'Maghrib',isyak:'Isyak'};
var ORDER = ['imsak','subuh','syuruk','zohor','asar','maghrib','isyak'];
var SEQ = ['subuh','syuruk','zohor','asar','maghrib','isyak'];
var WAJIB = {subuh:1,zohor:1,asar:1,maghrib:1,isyak:1};

function now(){ return Date.now() + offset; }
function loc(ms){ return new Date(ms + (D.tzo || 28800000)); }           // read with getUTC* = masjid local time
function pad(n){ return (n < 10 ? '0' : '') + n; }
function ymd(ms){ var d = loc(ms); return d.getUTCFullYear() + '-' + pad(d.getUTCMonth()+1) + '-' + pad(d.getUTCDate()); }
function hm(ms, withSuffix){
  var d = loc(ms), h = d.getUTCHours(), m = pad(d.getUTCMinutes());
  if (!D.cfg.h12) return pad(h) + ':' + m;
  var h12 = h % 12 || 12; return h12 + ':' + m + (withSuffix ? ' ' + period(h).toLowerCase() : '');
}
function period(h){ return h < 12 ? 'Pagi' : h < 14 ? 'Tengah hari' : h < 19 ? 'Petang' : 'Malam'; }
function dur(ms){ if (ms < 0) ms = 0; var s = Math.floor(ms/1000), h = Math.floor(s/3600), m = Math.floor(s%3600/60); s = s % 60; return (h ? pad(h) + ':' : '') + pad(m) + ':' + pad(s); }
function dayOf(date){ for (var i = 0; i < D.days.length; i++) if (D.days[i].date === date) return D.days[i]; return null; }
function el(tag, cls, txt){ var e = document.createElement(tag); if (cls) e.className = cls; if (txt != null) e.textContent = txt; return e; }

/* ---------- scale 1920×1080 stage to any TV viewport ---------- */
function fit(){ var s = Math.min(window.innerWidth/1920, window.innerHeight/1080); $('stage').style.transform = 'translate(-50%,-50%) scale(' + s + ')'; }
window.addEventListener('resize', fit); fit();

/* ---------- tiles ---------- */
var tiles = {};
ORDER.forEach(function(k){
  var t = el('div', 'tile' + (k === 'imsak' || k === 'syuruk' ? ' minor' : ''));
  var x = el('div', 'x'), n = el('div', 'n', NAMA[k]), v = el('div', 't num', '--:--'), c = el('div', 'c num');
  t.appendChild(x); t.appendChild(n); t.appendChild(v); t.appendChild(c); $('tiles').appendChild(t);
  tiles[k] = {el:t, x:x, n:n, v:v, c:c};
});

/* ---------- config-driven parts (rebuilt only when settings change) ---------- */
function infaqBlock(fixed){
  var f = D.cfg.infaq, w = el('div', fixed ? '' : 'infaq');
  var q = el('div', 'qr'), img = el('img'); img.src = f.qr; img.alt = 'DuitNow QR'; q.appendChild(img);
  if (fixed){
    w.appendChild(el('div', 'tag', f.tajuk || 'Infaq')); if (f.qr) w.appendChild(q);
    w.appendChild(el('div', 'b', f.bank)); w.appendChild(el('div', 'a num', f.akaun)); w.appendChild(el('div', 'nm', f.nama));
    return w;
  }
  var info = el('div');
  info.appendChild(el('div', 'tag', f.tajuk || 'Infaq'));
  info.appendChild(el('div', 'b', f.bank)); info.appendChild(el('div', 'a num', f.akaun));
  info.appendChild(el('div', 'k', 'Nama akaun')); info.appendChild(el('div', 'nm', f.nama));
  if (f.nota) info.appendChild(el('div', 'nt', f.nota));
  if (f.qr) w.appendChild(q); w.appendChild(info); return w;
}
function buildSlides(){
  var p = $('panel'); Array.prototype.slice.call(p.querySelectorAll('.slide')).forEach(function(s){ p.removeChild(s); });
  curSlides = [];
  (D.cfg.slides || []).forEach(function(s){
    var d = el('div', 'slide'); d.appendChild(el('div', 'tag', 'Makluman'));
    if (s.h) d.appendChild(el('h2', '', s.h)); if (s.b) d.appendChild(el('p', '', s.b));
    curSlides.push(d);
  });
  var mode = D.cfg.infaq.mode;
  document.body.classList.toggle('infaq-tetap', mode === 'tetap');
  if (mode === 'slaid'){ var d = el('div', 'slide'); d.appendChild(infaqBlock(false)); curSlides.push(d); }
  var fx = $('fixinfaq'); fx.innerHTML = ''; if (mode === 'tetap') fx.appendChild(infaqBlock(true));
  if (!curSlides.length){ var e = el('div', 'slide'); e.appendChild(el('h2', '', D.cfg.nama)); e.appendChild(el('p', '', 'Jom Rapatkan Saf!')); curSlides.push(e); }
  curSlides.forEach(function(s){ p.insertBefore(s, $('dots')); });
  var dots = $('dots'); dots.innerHTML = ''; if (curSlides.length > 1) curSlides.forEach(function(){ dots.appendChild(el('i')); });
  slideIdx = -1; nextSlide();
  if (slideTimer) clearInterval(slideTimer);
  slideTimer = setInterval(nextSlide, Math.max(5, D.cfg.slide_sec || 15) * 1000);
}
function nextSlide(){
  if (!curSlides.length) return;
  slideIdx = (slideIdx + 1) % curSlides.length;
  var dots = $('dots').children;
  for (var i = 0; i < curSlides.length; i++){ curSlides[i].classList.toggle('on', i === slideIdx); if (dots[i]) dots[i].classList.toggle('on', i === slideIdx); }
}
function buildMarquee(){
  var trk = $('mqtrk'), win = $('mqwin'), items = D.cfg.items || [];
  $('mqlb').textContent = D.cfg.label || 'Pengumuman';
  $('mqlb').style.display = D.cfg.label ? '' : 'none';
  trk.style.animation = 'none'; trk.innerHTML = '';
  if (!items.length) return;
  function addSet(){ items.forEach(function(it){ trk.appendChild(el('span', 'it' + (it.p ? ' p' : ''), it.t)); trk.appendChild(el('span', 'sp', '✦')); }); }
  addSet(); var guard = 0;
  while (trk.scrollWidth < win.clientWidth + 40 && guard++ < 20) addSet();   // one copy must be wider than the window
  var one = trk.scrollWidth; trk.innerHTML += trk.innerHTML;                  // duplicate for a seamless loop
  var secs = one / Math.max(20, D.cfg.speed || 90);
  void trk.offsetWidth;
  trk.style.animation = 'mq ' + secs.toFixed(2) + 's linear infinite';
}
function applyCfg(){
  if (D.ver === cfgVer) return; cfgVer = D.ver;
  $('nama').textContent = D.cfg.nama; $('lokasi').textContent = D.cfg.lokasi;
  if ($('logo').getAttribute('src') !== D.cfg.logo) $('logo').src = D.cfg.logo;
  buildSlides(); buildMarquee();
}

/* ---------- every second ---------- */
var lastDate = '';
function tick(){
  var t = now(), d = loc(t), today = ymd(t);
  var T = dayOf(today), Y = dayOf(ymd(t - 864e5)), M = dayOf(ymd(t + 864e5));

  // clock
  var h = d.getUTCHours();
  $('hm').textContent = D.cfg.h12 ? ((h % 12 || 12) + ':' + pad(d.getUTCMinutes())) : (pad(h) + ':' + pad(d.getUTCMinutes()));
  $('ss').textContent = pad(d.getUTCSeconds());
  $('pd').textContent = D.cfg.h12 ? period(h) : '';

  if (today !== lastDate){
    lastDate = today;
    $('greg').textContent = HARI[d.getUTCDay()] + ', ' + d.getUTCDate() + ' ' + BULAN[d.getUTCMonth()] + ' ' + d.getUTCFullYear();
    $('hijri').textContent = T && T.hijri ? T.hijri : '';
    $('est').style.display = T && T.est ? 'block' : 'none';
  }
  var jumaat = d.getUTCDay() === 5;
  tiles.zohor.n.textContent = jumaat ? 'Jumaat' : 'Zohor';

  if (!T){ ORDER.forEach(function(k){ tiles[k].v.textContent = '--:--'; tiles[k].el.className = tiles[k].el.className.replace(/ (cur|nxt)/g, ''); });
           $('nowtxt').textContent = 'Memuatkan waktu solat…'; $('nexttxt').innerHTML = '&nbsp;'; $('nextcd').textContent = '--:--:--'; return; }

  // current & next
  var cur = null, nxt = null, nxtT = 0, nxtTomorrow = false;
  for (var i = 0; i < SEQ.length; i++){ var k = SEQ[i]; if (T.t[k] && T.t[k] <= t) cur = k; }
  for (i = 0; i < SEQ.length; i++){ k = SEQ[i]; if (T.t[k] && T.t[k] > t){ nxt = k; nxtT = T.t[k]; break; } }
  if (!nxt && M && M.t.subuh){ nxt = 'subuh'; nxtT = M.t.subuh; nxtTomorrow = true; }
  var isyakY = false; if (!cur) { cur = 'isyak'; isyakY = !!(Y && Y.t.isyak); }   // after midnight, before Subuh
  var dhuha = cur === 'syuruk';

  ORDER.forEach(function(k){
    var o = tiles[k], ms = (k === 'subuh' && nxtTomorrow) ? nxtT : (k === 'isyak' && isyakY) ? Y.t.isyak : T.t[k];
    o.v.textContent = ms ? hm(ms) : '--:--';
    var c = 'tile' + (k === 'imsak' || k === 'syuruk' ? ' minor' : '');
    if (k === cur && !dhuha) { c += ' cur'; o.x.textContent = 'SEKARANG'; }
    if (k === nxt) { c += ' nxt'; o.x.textContent = nxtTomorrow ? 'ESOK' : 'SETERUSNYA'; o.c.textContent = dur(nxtT - t); }
    if (o.el.className !== c) o.el.className = c;
  });
  var nm = function(k){ return k === 'zohor' && jumaat ? 'Jumaat' : NAMA[k]; };
  $('nowtxt').innerHTML = dhuha ? 'Kini: <b>Waktu Dhuha</b>' : 'Waktu semasa: <b>' + nm(cur) + '</b>';
  $('nexttxt').innerHTML = '<b>' + nm(nxt || 'subuh') + '</b>' + (nxtTomorrow ? ' (esok)' : '') + ' dalam';
  var left = nxtT - t;
  $('nextcd').textContent = nxt ? dur(left) : '--:--:--';
  $('nextbox').className = 'nextbox' + (nxt && left < 10 * 60000 ? ' soon' : '');

  overlay(t, T, jumaat);
}

/* ---------- azan / iqamah / solat screen ---------- */
function overlay(t, T, jumaat){
  var on = false, solat = false, c = D.cfg;
  if (c.azan){
    var last = null;
    for (var k in WAJIB) if (T.t[k] && T.t[k] <= t && (!last || T.t[k] > T.t[last])) last = k;
    if (last){
      var at = T.t[last], el2 = t - at;
      if (jumaat && last === 'zohor'){
        if (el2 < c.jumaat_min * 60000){ on = true; solat = el2 > 2 * 60000;
          $('ovk').textContent = 'Telah masuk waktu'; $('ovw').textContent = 'Jumaat';
          $('ovs').textContent = 'Khutbah & Solat Jumaat'; $('ovcd').textContent = ''; $('ovm').textContent = 'Sila senyapkan telefon bimbit dan dengar khutbah dengan tekun'; }
      } else {
        var iq = (c.iqamah[last] || 0) * 60000;
        if (el2 < iq){ on = true;
          $('ovk').textContent = 'Telah masuk waktu'; $('ovw').textContent = NAMA[last];
          $('ovs').textContent = 'Iqamah dalam'; $('ovcd').textContent = dur(iq - el2); $('ovm').textContent = 'Luruskan dan rapatkan saf · Matikan telefon bimbit';
        } else if (el2 < iq + c.solat_min * 60000){ on = true; solat = true;
          $('ovk').textContent = 'Solat ' + NAMA[last]; $('ovw').textContent = 'Sedang didirikan';
          $('ovs').textContent = ''; $('ovcd').textContent = ''; $('ovm').textContent = 'Sila matikan telefon bimbit';
        }
      }
    }
  }
  document.body.classList.toggle('ov-on', on);
  document.body.classList.toggle('ov-solat', on && solat);
}

/* ---------- data refresh ---------- */
function load(){
  var t0 = Date.now(), x = new XMLHttpRequest();
  x.open('GET', API + (API.indexOf('?') > -1 ? '&' : '?') + '_=' + t0, true);
  x.timeout = 20000;
  x.onload = function(){
    if (x.status !== 200) return fail();
    try { var r = JSON.parse(x.responseText); } catch (e) { return fail(); }
    if (r.code && r.code !== D.code){ location.reload(); return; }   // plugin updated → reload page
    var t1 = Date.now(); offset = r.now - Math.round((t0 + t1) / 2) + shift;
    D = r; document.body.classList.remove('offline'); lastDate = '';
    try { localStorage.setItem('ptv', x.responseText); } catch (e) {}
    applyCfg();
  };
  x.onerror = x.ontimeout = fail; x.send();
}
function fail(){ document.body.classList.add('offline'); }

// prefer cached data if it covers more days than the embedded copy (e.g. TV rebooted offline)
try { var cached = JSON.parse(localStorage.getItem('ptv') || 'null'); if (cached && cached.days && !D.days.length) { D = cached; offset = 0; } } catch (e) {}

// test mode: /tv/?uji=17:30 (pretend it is that time today), &slaid=2 (start on slide 2)
(function(){
  var m = /[?&]uji=(\d{1,2}):?(\d{2})(?::(\d{2}))?/.exec(location.search);
  if (m){ var d = loc(now()); var target = Date.UTC(d.getUTCFullYear(), d.getUTCMonth(), d.getUTCDate(), +m[1], +m[2], +(m[3]||0)) - (D.tzo || 28800000);
    shift = target - now(); offset += shift; }
})();
applyCfg();
(function(){ var m = /[?&]slaid=(\d+)/.exec(location.search); if (m){ slideIdx = (+m[1] - 2); nextSlide(); } })();
tick();
setInterval(tick, 1000);
setInterval(load, 60000);
setInterval(function(){ var d = loc(now()); if (d.getUTCHours() === 3 && d.getUTCMinutes() === 30 && Date.now() - booted > 3600e3) location.reload(); }, 30000);

/* keep screen awake + fullscreen on OK/click (Android TV remote sends Enter) */
function wake(){ try { if (navigator.wakeLock) navigator.wakeLock.request('screen').catch(function(){}); } catch (e) {} }
function fs(){ var e = document.documentElement, f = e.requestFullscreen || e.webkitRequestFullscreen; if (f && !document.fullscreenElement) try { f.call(e); } catch (er) {} wake(); }
document.addEventListener('click', fs); document.addEventListener('keydown', function(e){ if (e.key === 'Enter' || e.keyCode === 13 || e.keyCode === 23) fs(); });
document.addEventListener('visibilitychange', function(){ if (!document.hidden) { wake(); load(); } });
wake();
})();
</script>
</body>
</html>
