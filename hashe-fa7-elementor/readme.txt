=== Hashe Font Awesome 7 Icon Library for Elementor ===
Contributors: Hashe Computer Solutions
Tags: elementor, icons, font-awesome, icon-library, fontawesome
Requires at least: 6.2
Requires PHP: 7.4
Requires Plugins: elementor
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds Font Awesome 7 Free Solid, Regular and Brands icons to Elementor's native Icon Library. Optional Pro catalogs list names only and require your own licensed Font Awesome Kit.

== Description ==

The plugin adds searchable Font Awesome 7.3.1 Free catalogs, including licensed Free CSS and webfonts, without requiring an account or Kit.

When an existing Font Awesome Kit ID is configured in Elementor, it also registers 16 additional names-only Pro style catalogs. The Kit must be licensed and configured to serve the selected styles. A Kit ID alone does not grant Pro access. When a Kit is configured, the plugin leaves font loading (including Free styles) to that Kit.

No Pro glyphs, Pro font files, Pro CSS or private credentials are included. Elementor core files and existing icon libraries are not replaced.

The plugin requires an active Elementor installation. Elementor Pro is not required for standalone Free catalogs. Kit integration availability depends on the Elementor environment.

== Installation ==

1. Download the WordPress-installable ZIP attachment from https://github.com/HasheCS/hashe-fa7-elementor/releases/latest . For v1.0.3 the attachment is hashe-fa7-elementor-v1.0.3.zip .
2. Do NOT install GitHub's "Code > Download ZIP" repository source archive.
3. In WordPress go to Plugins > Add New Plugin > Upload Plugin, select the release ZIP, then install and activate it.
4. With Elementor active, open a widget's Icon Library control to select Font Awesome 7 Free icons.
5. Optionally configure your existing licensed Font Awesome Pro Kit under Elementor > Settings > Integrations if available. The Kit must serve the selected families.

Back up production sites before upgrades and verify on your actual Elementor setup. Pro icon names may appear even when a Kit does not serve their glyphs.

== Frequently Asked Questions ==

= Do Free icons need a Font Awesome account or Kit? =
No. Without a Kit configured, the bundled Free fonts and CSS load locally.

= Does adding a Kit ID unlock Pro icons? =
No. The plugin lists names only. Actual Pro rendering depends on a compatible, licensed Kit and its configuration.

= Can I install the repository source ZIP directly? =
Use the versioned release attachment instead; the source archive is not the packaged WordPress plugin.

= What if icons are missing or blank? =
Check that Elementor is active, check the browser console, and for Kit-enabled installations confirm Kit entitlement, available styles, font loading and domain restrictions. Report reproducible problems at https://github.com/HasheCS/hashe-fa7-elementor/issues and do not publish private credentials.

== Changelog ==

= 1.0.3 (recommended release) =
* Prevent Elementor browser-storage quota errors by holding Hashe icon catalogs in editor memory.
* Remove only outdated Hashe icon-cache entries; preserve unrelated Elementor storage.
* Keep per-category JSON fetching and correct concurrent callback association.
* Automated PHP smoke tests and simulated JavaScript storage/callback regression tests passed; PR #1 records Chromium checks.
* Amari site owner confirmed the editor works following installation (2026-10-26). Other installations are not exhaustively tested.

= 1.0.2 (development-only candidate; not separately released on GitHub) =
* Restored per-category JSON loading and addressed callback races.
* Required further logged-in Elementor UI verification.

= 1.0.1 (withdrawn, do not install) =
* Bulk inline icon catalogs caused an Elementor editor regression.
* Public release installer removed; Git source history retained.

= 1.0.0 (superseded historical baseline) =
* Initial Font Awesome 7 Free catalogs and optional Pro names-only integration.
* Not recommended for new installations; compatibility testing was limited.
