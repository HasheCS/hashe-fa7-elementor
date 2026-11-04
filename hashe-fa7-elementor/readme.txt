=== Hashe Font Awesome 7 Icon Library for Elementor ===
Contributors: Hashe Computer Solutions
Tags: elementor, icons, font-awesome, icon-library
Requires at least: 6.8
Tested up to: 7.1
Requires PHP: 7.4
Requires Plugins: elementor
Stable tag: 1.1.3
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

1. Download the installable plugin ZIP from the latest GitHub release, not GitHub's automatically generated source ZIP.
2. In WordPress, go to Plugins > Add New Plugin > Upload Plugin, choose the ZIP, then install and activate.
3. For Free icons, no Font Awesome Kit is needed. To use Pro or Pro Plus, configure your own licensed Font Awesome Kit ID in Elementor Pro.
4. Open Elementor, select an icon from the library, and save your page.

== Privacy and optional reporting ==

Icon libraries, licensed Font Awesome Kit features, and WordPress update checks do not require optional analytics. The plugin does not send installation reports without your affirmative, administrator-authorized opt-in. Go to Settings > Hashe FA7 Privacy to review status or choose Skip. Where no reviewed, operational reporting contract and privacy policy are installed, optional reporting cannot be enabled and the Allow action is unavailable. A failed reporting service never blocks icon selection or stable updates. Normal WordPress update checks use HTTPS and may be present in service access logs even when optional reporting is declined. If reporting is enabled in a future approved integration, a site administrator can revoke it at any time; any incomplete remote deletion request is shown as pending, not falsely declared successful.

== External services and distribution channels ==

Font Awesome API (https://api.fontawesome.com): When generating or refreshing a requested icon library, the plugin requests public release icon-name, family-style, and icon-pack metadata from Font Awesome. These requests use HTTPS and do not intentionally include the site's domain, WordPress users, or plugin analytics identity. The remote provider may receive normal network information including the requesting server IP address. Review Font Awesome's terms and privacy policy before enabling Kit-based functions: https://fontawesome.com/tos and https://fontawesome.com/privacy.

Configured Font Awesome Kit and uploads (Font Awesome kit delivery and https://kit-uploads.fontawesome.com): When a site administrator has configured a Kit, licensed assets are delivered by Font Awesome under that administrator's Kit configuration. Detection of uploaded custom icon layering may request Kit-hosted SVG files; the Kit identifier is included because Font Awesome needs it to locate those assets. A separate customer-owned license is needed for Pro/Pro Plus. The plugin does not bundle paid fonts or upload the site's content to Hashe.

Hashe optional installation reporting (https://plugins.hashe.com): No optional installation-reporting request is made unless the administrator explicitly grants informed consent. Reporting is currently unavailable because the receiving enrollment and approved privacy contract have not been activated. Skip is available; if an approved integration is activated in a future release, Revoke will be available. For disclosures and the operator's policy see https://plugins.hashe.com/privacy. This service is not required for icon libraries or updates.

Distribution: The independent GitHub/Hashe edition uses a checked GitHub stable ZIP through https://plugins.hashe.com/ and the WordPress native Update URI integration. The WordPress.org directory edition uses only WordPress.org for plugin update installation; neither edition requires optional telemetry consent to update. Only one edition of this identically named plugin can be installed at a time. Switching edition requires an administrator-controlled, backed-up installation decision and is not performed silently.
== Frequently Asked Questions ==

= Does the plugin include Pro Plus fonts? =
No. Paid glyphs are served by the customer's licensed Font Awesome Kit. The public release metadata provides names and available families.

= Why do native Elementor Free libraries appear with a Pro Plus-only Kit? =
Regular, Solid, and Brands are Elementor's native baseline. When a selected-style Kit omits those Pro families, the plugin preserves Elementor's original Free library definitions and fonts.

== Changelog ==

= 1.1.3 =
* Add the separated WordPress native updater with approved GitHub ZIP SHA-256 verification and an administrator-only, default-off optional installation-reporting consent interface. Reporting cannot be enabled until its service contract and privacy terms have been approved.
* Minimum WordPress 6.8 is aligned with Elementor 4.3.5 dependency requirements; PHP 7.4 remains the declared floor.
* Always expose the complete bundled Font Awesome 7 Free Solid, Regular, and Brands catalogs alongside any configured Kit, with isolated Free font classes to prevent licensed-Kit conflicts.
* Discover Kit-owned custom icons and custom Duotone icons under My Libraries for subsetted CSS/JS and full SVG+JS Kits, using only the current customer's published Kit assets.
* Preserve all 37 licensed family-styles on a full Pro Plus Kit, without bundling or redistributing paid glyphs.
* Keep Free and custom icon selection, saved values, frontend compatibility, and Elementor's existing native library behavior.
* Correct full SVG+JS custom-icon discovery by checking Kit-hosted SVG uploads rather than an unavailable companion CSS endpoint.
* Large uploaded-icon inventories and unusual custom SVG layers require further performance and compatibility verification.
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
