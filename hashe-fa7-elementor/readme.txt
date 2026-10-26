=== Hashe Font Awesome 7 Icon Library for Elementor ===
Contributors: Hashe Computer Solutions
Tags: elementor, icons, font-awesome, icon-library, fontawesome
Requires at least: 6.2
Requires PHP: 7.4
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds Font Awesome 7 Free catalogs to Elementor's native Icon Library, with additional names-only Pro catalogs when an existing Kit ID is configured.

== Description ==

No Kit required for Free icons. Free CSS and webfonts are bundled under their original Font Awesome Free licenses.
With a Kit ID already configured in Elementor, the plugin also exposes Pro style catalogs.
Pro icons require a compatible licensed Kit; the plugin does not include Pro assets or bypass any license.
Existing Elementor icon tabs remain intact.

== Installation ==

1. Upload the hashe-fa7-elementor directory as a plugin ZIP.
2. Activate the plugin.
3. Use Elementor's Icon Library.
4. Optionally add your own Pro Kit ID under Elementor Settings > Integrations.

== Changelog ==

= 1.0.3 =
* Prevent Elementor localStorage quota errors by holding Hashe icon catalogs in editor memory.
* Remove only outdated Hashe icon-cache entries; preserve other Elementor storage.
* Keep existing per-category fetching and correct concurrent callback associations.
* Amari site owner confirmed the editor works after v1.0.3 installation (2026-10-26).

= 1.0.2 =
* Preserve lightweight JSON catalog loading and fix callback races between concurrent icon-picker requests.
* Restore Elementor editor responsiveness; no Kit or core modifications.
* This is a candidate update requiring logged-in Elementor UI verification.

= 1.0.1 =
* Withdrawn: bulk inline icon catalogs caused a blank Elementor controls panel on Amari.
* Do not use this version.

= 1.0.0 =
* Public-ready standalone Free icon picker.
* Optional licensed Pro catalog integration.
* Removed staging-only and Elementor-Pro-only requirements.
