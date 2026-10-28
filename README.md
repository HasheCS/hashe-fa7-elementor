# Hashe Font Awesome 7 Icon Library for Elementor

GNU GPL WordPress plugin extending **Elementor's Icon Library** with Font Awesome 7 libraries, keeping Elementor's native icon definitions intact.

**Stable v1.1.1 — user accepted on Solutions Moco, October 29, 2026.**

## Operating modes

### No Kit ID

Adds Font Awesome Free **Solid**, **Regular** and **Brands** under My Libraries. The included Font Awesome Free 7.3.1 CSS, fonts and license notices are the original licensed Free assets. No paid Kit or remote metadata request is required.

### Customer's own Font Awesome Kit

Reads the Kit ID from Elementor Pro's existing integration option and checks styles the Kit actually serves. Selected-style CSS Kits yield their included styles; supported full Pro JS Kits yield up to 17 standard styles. No Kit ID is hardcoded in the distributed plugin.

Icon names and Free/Pro style availability come from Font Awesome's release-specific **public metadata REST API**, not bundled Pro fonts or datasets. WordPress caches valid metadata seven days, with up to 30 days of last-successful fallback for API outages. Kit selection changes are checked as the editor opens.

**The Kit remains the license and glyph-delivery boundary.** No paid Pro fonts, SVGs, paid stylesheets, bulk Pro metadata catalogs, or direct font-shard recovery requests for styles omitted from a user's Kit are bundled or used. Version awareness covers known supported Font Awesome 7.x style prefixes, not all future versions or Pro+ exclusive styles.

### Native Elementor compatibility

The original Elementor native library definitions remain untouched. If a CSS Kit enables only **Sharp** styles, native Classic/Pro previews can use the closest **already-enabled Sharp font**, without obtaining unavailable fonts:

- Solid → selected Sharp Solid or closest available Sharp.
- Regular → selected Sharp Regular or closest available Sharp.
- Light → selected Sharp Light or closest available Sharp.
- Duotone → a **single-color Sharp substitute**, not genuine layered Duotone.
- Native Brands → the included Font Awesome Free Brands CSS when omitted by the Kit.

When the matching Classic or Duotone font is actually enabled, no Sharp fallback is added for that style. When no Kit is configured, Free mode does not apply a Sharp fallback. The Kit-only in-memory catalog adapter protects against browser localStorage exhaustion on the 17-style Kit, without changing native Elementor persistence.

## Installation

1. Back up the WordPress site and its existing plugin installation.
2. Download the **separate installable plugin ZIP asset** from the v1.1.1 GitHub release, not GitHub's automatic source-code ZIP.
3. Go to WordPress **Plugins → Add New Plugin → Upload Plugin**, then install/replace and activate.
4. For Free mode, leave the Elementor Pro Font Awesome Kit ID empty. For licensed styles, configure your own authorized Kit.
5. Reopen/hard-refresh the Elementor editor after changes and check the icon picker and saved frontend rendering.

## Verification

- Visually accepted by the user on Solutions Moco: **no Kit, full Kit, small Sharp Kit** (October 29, 2026).
- Accepted QA14 code commit: f90887482c9dc9fa9ae1861c0d2d2524772193e1.
- Automated PHP 7.4, 8.2, 8.3 and JS CI; nine registration/cache/entitlement scenarios; real public Font Awesome API pagination test.
- The stable version bump changes documentation/version markers, not the accepted QA14 functionality.
- No separate clean-site install or Pro+ exclusive licensed-style test is claimed.

## Licenses and trademarks

Our integration code: GPL-2.0-or-later. Original Font Awesome Free fonts, icons and CSS retain their applicable SIL OFL 1.1, CC BY 4.0, and MIT terms, described in the bundled [Font Awesome license notice](hashe-fa7-elementor/FONT-AWESOME-LICENSE.txt).

Font Awesome and Elementor are third-party trademarks. No endorsement or special licensing permission is implied.
