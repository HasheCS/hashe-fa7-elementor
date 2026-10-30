=== Hashe Font Awesome 7 Icon Library for Elementor ===
Contributors: Hashe Computer Solutions
Tags: elementor, icons, font-awesome, icon-library
Requires at least: 6.2
Requires PHP: 7.4
Requires Plugins: elementor
Stable tag: 1.1.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Extend Elementor's Icon Library with Font Awesome 7 Free, Pro, and Pro Plus styles available through your own Kit.

== Description ==

This plugin adds Kit-dependent Font Awesome 7 icon libraries to Elementor. Font Awesome's official public release metadata supplies icon names and style families dynamically. The customer-owned Kit remains the authority for paid styles and font delivery.

* No Kit: Elementor's three native Free libraries remain available; the plugin adds separate Font Awesome 7 Free Solid, Regular, and Brands libraries under My Libraries.
* Selected-style Kit: Elementor's three native Free libraries remain. Classic Light and Duotone native Pro entries are shown only if those styles are published in the Kit. Other selected style families appear under My Libraries.
* Whole-library Kit: Native Classic Solid, Regular, Light, Duotone Solid, and Brands stay in Elementor's native section; other available styles appear under My Libraries.
* When a selective Kit omits a native Free family, Elementor's bundled original Free font assets support it without downloading unselected Pro fonts.
* The plugin rechecks a Kit's published revision in the editor and caches the official release catalog locally. It does not rewrite saved icon references or Elementor core/plugin files.

No paid font binaries, SVG glyphs, licensed Pro stylesheets, or copied Pro icon catalog data are included. Elementor and Font Awesome are third-party trademarks; no endorsement is claimed.

== Installation ==

1. Back up the WordPress site and current plugin.
2. Download the installable plugin ZIP asset from the v1.1.2 GitHub release (not GitHub's automatic repository source ZIP).
3. In WordPress select Plugins > Add New > Upload Plugin, install/replace, and activate.
4. Leave Elementor Pro's Font Awesome Kit ID empty for Free mode, or provide your own licensed Font Awesome Kit ID.
5. Reopen Elementor after Kit changes. Verify native and additional libraries, then save and reload an icon widget.

== Frequently Asked Questions ==

= Does the plugin include Pro Plus fonts? =
No. Paid glyphs are served by the customer's licensed Font Awesome Kit. The public release metadata provides names and available families.

= Why do native Elementor Free libraries appear with a Pro Plus-only Kit? =
Regular, Solid, and Brands are Elementor's native baseline. When a selected-style Kit omits those Pro families, the plugin preserves Elementor's original Free library definitions and fonts.

== Changelog ==

= 1.1.2 =
* Dynamically discover all Font Awesome 7 family-styles and Pro/Pro Plus collections using official release-specific public metadata instead of a 17-style fixed map.
* Register only icon styles actually served by the configured Kit; no hardcoded Kit IDs.
* Preserve native Regular, Solid, and Brands using Elementor Core's original Free definitions when a selective Kit omits those families.
* Show native Light and Duotone only when enabled by the Kit, without duplicating native categories under My Libraries.
* Correct native Free font-family compatibility alongside a selected Pro Plus Kit; retain saved icon markup and frontend rendering.
* Refresh cached Kit style inventory when its published configuration changes.
* Owner visually accepted the no-Kit, small Kit and full Pro Plus Kit modes on Solutions playground October 31, 2026.

= 1.1.1 =
* Font Awesome 7 Free and Kit-dependent Pro libraries, official public icon-name metadata, and scoped editor memory adapter.
* Original licensed Free assets and license notices; no paid Font Awesome fonts or SVGs distributed.
