=== Hashe Font Awesome 7 Icon Library for Elementor ===
Contributors: Hashe Computer Solutions
Tags: elementor, icons, font-awesome, icon-library
Requires at least: 6.2
Requires PHP: 7.4
Requires Plugins: elementor
Stable tag: 1.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Extend Elementor's native icon picker using Font Awesome Free or your own licensed Font Awesome Kit.

== Description ==

This GNU GPL integration adds Font Awesome 7 libraries to Elementor without changing Elementor's native library definitions.

* With no Kit ID: Font Awesome Free 7.3.1 Solid, Regular and Brands, using the bundled original licensed Free assets.
* With a user-owned Kit ID: detect the published styles, then retrieve icon names from Font Awesome's version-specific public REST metadata API.
* Cache public metadata on the WordPress site; reuse last-known-good results during temporary API failures.
* Use existing enabled Sharp fonts as a visual fallback for native Classic/Duotone previews if a subsetted Kit omits the corresponding Classic fonts. Duotone appears as single-color Sharp, not genuine layered Duotone.
* Full 17-style Kit uses a narrowly scoped memory adapter against localStorage quota errors.
* No paid fonts/SVGs, copied Pro catalogs, or requests to omitted Pro font shards.

The customer's Font Awesome Kit supplies paid glyphs and determines usable styles. Known Font Awesome 7.x style prefixes are supported; Pro+ exclusive styles and arbitrary future major versions are not verified.

== Installation ==

1. Back up your WordPress site. Download the approved installable ZIP attached to the v1.1.1 release, not GitHub's source-code ZIP.
2. Open Plugins > Add New Plugin > Upload Plugin; install, replace if upgrading, then activate.
3. Leave the Kit ID empty in Elementor Pro integrations for Free mode, or configure your own licensed Kit.
4. Reopen Elementor and check the picker and existing saved icons after changing a Kit.

== Frequently Asked Questions ==

= Does this plugin include Pro fonts? =
No. Only the user's licensed Kit supplies paid glyphs. The included Free fonts retain Font Awesome's original Free licenses.

= What if my Kit includes only Sharp styles? =
The native Classic library definitions remain unchanged. Their icon previews can use the closest already-enabled Sharp font, and native Duotone is rendered as one color.

== Changelog ==

= 1.1.1 =
* Visually accepted by the user on Solutions Moco for no Kit, full Kit and small Kit.
* Kit-dependent libraries and version-scoped official metadata API with site-local caching.
* Three Free-only libraries and bundled licensed Free assets when no Kit is set.
* Fix numeric icon names causing Elementor trim exceptions.
* Correct Brands navigation icon.
* Native Classic and Duotone visual fallback using only selected Sharp Kit fonts.
* Preserve native Elementor definitions and the large-Kit in-memory adapter.
* No paid font binaries or bundled copied Pro metadata.

= 1.1.0 =
* Previous additive Kit libraries and editor memory work.
