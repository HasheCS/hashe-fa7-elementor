=== Hashe Font Awesome 7 Icon Library for Elementor ===
Contributors: Hashe Computer Solutions
Tags: elementor, icons, font-awesome, icon-library, fontawesome
Requires at least: 6.2
Requires PHP: 7.4
Requires Plugins: elementor
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds the supported Font Awesome 7.3.1 styles actually available in the Elementor-configured Font Awesome Kit below My Libraries, preserving Elementor's five native libraries.

== Description ==

This plugin extends Elementor's own icon picker. When Elementor Pro has a compatible licensed Font Awesome Kit ID, it detects the supported published styles and registers only those styles as additional libraries below My Libraries.

Elementor's native icon libraries stay above My Libraries. Users can switch Kits and change style selections within the same Kit; the plugin rechecks the published revision when the editor opens.

The full 17-style Font Awesome Pro Kit and small subsetted CSS Kits were tested with Elementor Core 4.3.5, Elementor Pro 4.3.2 and Font Awesome 7.3.1 on a development site. Pro+ exclusive styles and other versions are not verified.

No Pro fonts, SVG glyphs, Pro CSS distributions, or Kit credentials are bundled. Actual rendering requires the user's own licensed Kit. Unmodified Font Awesome Free webfonts and CSS carry their original licensing notices.

Without a supported Kit the plugin adds no custom libraries and leaves Elementor alone.

== Installation ==

1. Download hashe-fa7-elementor-v1.1.0.zip from https://github.com/HasheCS/hashe-fa7-elementor/releases/tag/v1.1.0 . Use the versioned release asset, not GitHub's automatic source archive.
2. Back up your WordPress site. Go to Plugins > Add New Plugin > Upload Plugin, install the ZIP and activate it with Elementor active.
3. Configure your own compatible Font Awesome Kit ID in Elementor Pro's integration settings.
4. Reopen the Elementor editor and inspect an Icon Library control. Kit styles appear below My Libraries; native Elementor tabs remain above.

== Frequently Asked Questions ==

= Does the plugin work with any Kit ID? =
The Kit ID itself is not hardcoded. The plugin detects the actual Kit's supported Font Awesome 7.3.1 styles. Unsupported versions, incomplete Kit metadata, and Pro+ exclusives are not guaranteed.

= Does adding a Kit ID grant Pro icon access? =
No. A Kit must be licensed and authorized separately. The plugin supplies names-only metadata, not Pro assets or entitlements.

= Why are some native icons missing from a subsetted Kit? =
A subsetted Kit may omit font families used by Elementor's own interface. The plugin loads the unmodified Free Brands asset as needed and uses the configured Kit for select missing native Pro families.

= How are large icon libraries handled? =
A narrowly scoped memory adapter handles only this plugin's added Kit catalogs. Elementor's native icon storage and picker are preserved.

== Changelog ==

= 1.1.0 =
* Register only the actual supported Kit styles below My Libraries; preserve all five native Elementor libraries.
* Detect published changes within the same Kit ID during editor initialization.
* Handle the verified 17-style Pro Kit without browser localStorage quota errors.
* Restore native sidebar Brands dependency using unmodified Font Awesome Free CSS, not a custom label override.
* Include scoped compatibility recovery for missing native Pro font families with the user's licensed Kit.
* QA11 visually accepted on Solutions Moco 2026-10-28, with release CI checks.

= 1.0.3 (historical) =
* Earlier architecture and browser storage fixes. Not the current recommended release.

= 1.0.1 (withdrawn) =
* Caused an Elementor editor regression. Do not install.
