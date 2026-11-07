# Changelog

## 1.0.0 — 2026-11-07
First public release (generalised from the Perepat Paparan TV 1.0.0 site build).
- Built-in JAKIM e-Solat prayer times for any zone (full zone list), stored per month, twice-daily refresh, waktusolat.app fallback, estimate mode when offline; status + manual refresh in the settings page
- Generic defaults (site name, built-in white masjid logo, no bank/QR until set — Infaq hidden when empty), logo picker
- REST namespace `paparan-tv/v1`, `ptv_slug` and `ptv_roles` filters, activation notice, uninstall cleanup
- Built-in GitHub Releases updater; refuses to activate alongside the old site-specific build
