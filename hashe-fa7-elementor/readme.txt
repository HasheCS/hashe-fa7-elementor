=== Hashe Font Awesome 7 Icon Library for Elementor ===
Contributors: Hashe Computer Solutions
Tags: elementor, icons, font-awesome, icon-library, fontawesome
Requires at least: 6.2
Requires PHP: 7.4
Requires Plugins: elementor
Stable tag: 1.1.0-beta.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Development-only integrated Font Awesome 7 Kit discovery for Elementor. Do NOT present this branch as a complete, signed-off installer.

== Description ==

The Hashe plugin adds Font Awesome 7 icon catalogs to Elementor's existing Icon Library. It does not modify Elementor core or require a second customer account, license selector, API token or configuration.

WITHOUT an Elementor Font Awesome Kit ID, it offers three packaged Font Awesome 7.3.1 Free catalogs: Solid, Regular and Brands, with matched Free webfonts and CSS.

WITH an existing Font Awesome Kit ID (configured in Elementor's native integrations), it reads the public Kit bootstrap and official version-specific Font Awesome release metadata:

* For Web Fonts with a JS Kit embed: discovers selected styles from the Kit stylesheet font-face rules. The JavaScript EMBED does not imply SVG delivery.
* For a full Web Fonts Kit: uses the hosted full CSS when the Kit does not contain a subset path.
* For a Kit-subsetted Web Fonts style: intersects release-specific icon names with the public Kit CSS icon names.
* For a By Icon Kit involving only one style: filters to the individually listed names. A multi-style By Icon Kit can publish a global CSS icon-name map without exact per-style membership; the plugin rejects ambiguous catalog requests rather than creating misleading icons.
* For a full SVG+JS Pro/Free Kit: recognizes the Kit's official full-release startup JS bundle and registers verified standard family-styles from public Font Awesome metadata. A custom subsetted SVG Kit is not presumed to include all icons or styles.
* Pro Plus-only families are not inferred from an ordinary Pro entitlement; eligible styles depend on the Kit's actual hosted assets.

Exact SVG By Icon subset inventories, Pro Plus-only Kit entitlements, full editor/frontend SVG rendering, and arbitrary customer domain permissions are **NOT YET universally verified**. They are remaining release gates, not claimed features.

The plugin adds Hashe-specific tabs and does not remove Elementor's five original Pro tabs or other providers. It no longer adds global CSS overrides for native classes such as .fas/.far/.fal/.fad. Missing genuine paid glyph fonts cannot be fabricated or redistributed.

The editor uses scoped in-memory caching and an asynchronous callback guard for icon-library loads. The server supplies a canonical tab list so a corrupted client picker list can be restored without erasing unrelated icon libraries.

Catalog metadata is retrieved from official Font Awesome public REST endpoints, validated across all pages, cached by exact Font Awesome release version and retained as last-known-good on refresh failures. WP-Cron attempts hourly prewarming, while editor catalog requests provide the guaranteed on-demand fallback when cron is disabled or traffic is sparse. Built-in Free font assets only upgrade with a tested plugin release; they are not silently replaced by REST metadata.

== Installation ==

This integrated feature branch is currently NOT a public release or approved WordPress installer. Use the existing tagged v1.0.3 release for public installation until the 24-case PPP matrix is complete.

For a later approved versioned plugin ZIP, install via WordPress Plugins > Add New Plugin > Upload Plugin. Configure Kit ID, if any, in the existing Elementor Settings > Integrations > Font Awesome Pro field. Do not upload GitHub's source-repository ZIP.

The customer's Kit assets are hosted and licensed by Font Awesome. A Kit may restrict the web domains allowed to use it. The plugin sends this WordPress site's own URL as Referer while retrieving its Kit bootstrap; another domain cannot be used to bypass domain restrictions.

== Frequently Asked Questions ==

= Do I need a Kit for the Free catalogs? =
No. Three bundled Free catalogs and CSS/fonts work without a Kit.

= Does a Kit ID by itself unlock paid icons? =
No. The Kit's actual enabled families, icon selection, license and domain restrictions control what can render.

= Are original Elementor libraries replaced? =
No. They remain registered. Existing saved icon references are not rewritten.

= Why are original Elementor Light/Duotone Pro icons blank with a two-style Kit? =
Those native Elementor tabs may be present even if the configured Kit supplies only Brands and Sharp Duotone Regular. Our plugin does not create missing paid Classic fonts; an authenticated before/after test with Hashe skipped is required to distinguish external behavior from a regression.

= Which modes are release-blocked? =
Unverified exact SVG By Icon inventory and Pro Plus-only style manifest, authenticated Elementor SVG picker and frontend saves, multi-style By Icon CSS precision, and the full cross-browser PPP acceptance. See the approved engineering blueprint and the acceptance register before promoting a release.

== Licenses and attribution ==

The plugin is GPL-2.0-or-later. Bundled Font Awesome Free assets follow Font Awesome's MIT, SIL OFL 1.1 and CC BY 4.0 terms as applicable. No paid Font Awesome CSS, fonts, SVG paths, private API credentials or paid assets are bundled. Font Awesome and Elementor are registered marks of their respective owners; Hashe Computer Solutions is not affiliated with or endorsed by either provider.

== Changelog ==

= 1.1.0-beta.4 integrated engineering development (not released) =
* Consolidate original Elementor icon-picker callback, duplicate-tab and storage repairs.
* Remove beta.3 global .fas/.far/.fal/.fad paid-font substitution rules.
* Read-only two-style Web Fonts test Kit and full SVG Kit public metadata verification.
* Dynamic per-version, per-style public REST catalogs with Free license filtering and last-known-good cache.
* Use current site's Referer for authorized domain-restricted Kit discovery.
* Restrict metadata requests to Font Awesome's HTTPS destinations and privileged catalog AJAX calls to logged-in editor sessions and WordPress nonces.
* Add full Web Fonts and ordinary Pro/Free SVG Kit modes; fail closed for unsupported exact SVG subsets.
* Add bounded background catalog prewarming and Elementor on-demand fallback.

= 1.0.3 (public recommended release) =
* Prevent Elementor browser storage quota errors with editor-scoped in-memory catalogs.
* Preserve unrelated Elementor library caches.
* Confirmed working by the site owner on Amari on 26 October 2026; this was not universal Kit-mode acceptance.

= 1.0.1 (withdrawn; do not install) =
* Regressed Elementor editor functionality. Withdrawn installer retained only in source history.
