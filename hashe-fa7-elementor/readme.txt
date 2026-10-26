=== Hashe Font Awesome 7 Icon Library for Elementor ===
Contributors: Hashe Computer Solutions
Tags: elementor, icons, font-awesome, icon-library, fontawesome
Requires at least: 6.2
Requires PHP: 7.4
Requires Plugins: elementor
Stable tag: 1.1.0-beta.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Amari test beta: Free icon catalogs without a Kit; with an Elementor Kit ID, inspect the Kit's selected Web Font styles and register only matching names-only catalogs.

== Description ==

BETA WARNING: This 1.1.0-beta.2 package is intended for controlled testing on Amari only. It is not the public recommended release. The plugin adds searchable Font Awesome 7.3.1 Free catalogs, including licensed Free CSS and webfonts, without requiring an account or Kit.

When an existing Font Awesome Kit ID is configured in Elementor, this beta reads its public Javascript Kit configuration and selected Web Font stylesheet. It registers only family styles detected through that stylesheet. Only names are registered; Font Awesome remains responsible for serving licensed fonts. Changes to a Kit are rechecked after a short cache interval (up to 15 minutes) when the Elementor editor is reopened.

Web Fonts by-style Kits are the test target. SVG Kits, by-icon subsets and custom uploaded icon sets are NOT YET SUPPORTED or verified in this beta. If selected styles cannot be read, the beta registers no Kit-specific tabs; it does not assume a Pro license.

No Pro glyphs, Pro font files, Pro CSS or private credentials are included. Elementor core files and existing icon libraries are not replaced.

The plugin requires an active Elementor installation. Elementor Pro is not required for standalone Free catalogs. Kit integration availability depends on the Elementor environment.

== Installation ==

1. For this Amari beta use only the versioned testing ZIP from the feature-branch GitHub Actions artifact; do not install the public v1.0.3 ZIP for beta testing.
2. Do NOT install GitHub's "Code > Download ZIP" repository source archive.
3. In WordPress go to Plugins > Add New Plugin > Upload Plugin, select the release ZIP, then install and activate it.
4. With Elementor active, open a widget's Icon Library control. For the two-style test Kit 23f6a60c28, expect Hashe tabs for Brands and Sharp Duotone Regular; existing Elementor-native tabs are preserved.
5. Set your existing Kit ID in Elementor > Settings > Integrations. For this beta, use a by-style Web Fonts Kit first. If no Kit is set, Hashe's three Free tabs appear.
6. Before installing, take and verify a backup of the working Amari v1.0.3 plugin folder or WordPress site. If any Elementor editor issue occurs, deactivate this beta and reinstall the verified v1.0.3 package.

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

= 1.1.0-beta.2 (Amari test only) =
* Fix missing icons beside Hashe Kit libraries by using Elementor's own eicon-font-awesome glyph instead of relying on an excluded Font Awesome Kit style.
* Fix doubled Brands Brands category name; retain both selected Kit tabs and Elementor's original native tabs.
* Native Pro tab glyphs still depend on the customer's selected Kit styles; this fix does not add unselected Pro font assets.
* Public Font Awesome Kit Web Fonts style discovery; only explicitly enabled family-styles get added to the Elementor picker.
* New family styles may be detected from the current Font Awesome REST release catalog without distributing Pro assets.
* Per-category icon-name loading; cached Kit refresh; browser localStorage protection preserved.
* SVG and by-icon Kit modes are not yet verified or supported in this beta.

= 1.0.3 (previous recommended public release) =
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
