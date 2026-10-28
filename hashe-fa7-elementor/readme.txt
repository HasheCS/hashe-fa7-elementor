=== Hashe Font Awesome 7 Icon Library for Elementor ===
Contributors: Hashe Computer Solutions
Tags: elementor, icons, font-awesome, icon-library
Requires at least: 6.2
Requires PHP: 7.4
Requires Plugins: elementor
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Development candidate 1.1.1-rc.1: Adds Font Awesome Free Solid, Regular and Brands with no Kit, and licensed-Kit-dependent dynamic icon catalogs with per-site public API metadata caching.

== Description ==

This development candidate is pending visual acceptance and is NOT a published stable release.

With no Font Awesome Kit ID in Elementor, it adds Free Solid, Regular and Brands libraries using the bundled, properly licensed original Font Awesome Free 7.3.1 assets. No paid license or API account needed.

When a customer configures a supported Font Awesome 7.x Kit, the plugin inspects its actual published styles, retrieves icon names only from Font Awesome's official public REST API, and uses local WordPress caching. The customer's Kit alone supplies any paid glyphs. No Pro binaries or copied Pro catalogs are distributed.

The native Elementor libraries remain structurally untouched, although native Pro icons for styles missing from a subsetted Kit may be unavailable because Elementor Pro delegates font delivery to the Kit. The plugin does not bypass style selection by fetching omitted Pro fonts.

== Installation ==

1. Use an approved versioned WordPress plugin ZIP after backing up the site; do not upload the repository source ZIP.
2. Activate with Elementor. Leave the Kit field empty for Free-only mode.
3. For Pro styles, supply your own licensed Font Awesome Kit ID under Elementor Pro integrations and reopen the editor.
4. Confirm native and added libraries and saved frontend icon rendering.

== Changelog ==

= 1.1.1-rc.1 (development candidate; no public release yet) =
* Add licensed Font Awesome Free catalogs and local CSS/webfonts in no-Kit mode.
* Retrieve Pro names from public version-scoped Font Awesome REST metadata with site-local cached fallback.
* Remove all distributed copied Pro names and Pro Unicode/shard maps.
* Remove direct paid-font fallback for unselected Kit styles, respecting customer Kit selection.
* Preserve original native Elementor library objects and Kit-only large-catalog memory adapter.

= 1.1.0 =
* Previously accepted QA11 Kit style detection and large Kit support.
