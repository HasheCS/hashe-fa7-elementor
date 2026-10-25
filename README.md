# Hashe Font Awesome 7 Icon Library for Elementor

Make the Font Awesome 7 icon catalog searchable in **Elementor's native Icon Library**.

Elementor can accept a Font Awesome Kit ID without listing every newer Font Awesome 7 icon in its picker. This plugin adds searchable Font Awesome 7 tabs without modifying Elementor core files.

## What it does

- **Without a Kit:** Adds the complete Font Awesome 7.3.1 **Free** Solid, Regular, and Brands icon catalogs. Bundled, officially licensed Free webfonts and CSS render them without an account.
- **With an Elementor Kit ID:** Keeps the Free catalogs and adds sixteen **Pro** family/style catalogs, including Classic, Duotone, Sharp, and Sharp Duotone. Pro glyph rendering uses the site's **own licensed Font Awesome Kit**.
- Preserves Elementor's existing icon libraries and saved icons.
- No API key stored by this plugin, no options page, no Elementor core patch.

> **Important licensing distinction:** Setting a Kit ID does not, by itself, prove that the Kit includes Pro access. Pro icons will display correctly **only** where the connected Kit is licensed and configured to load the applicable families. This plugin does not grant access to Pro, distribute Pro font or SVG files, or bypass Font Awesome licensing.

## Installation

1. From GitHub, select **Code → Download ZIP**; extract it, then ZIP **only** the `hashe-fa7-elementor` folder (or use a packaged release ZIP when available).
2. WordPress → **Plugins → Add New → Upload Plugin**; install and activate the ZIP.
3. Open a page with Elementor, select a widget with an Icon Library control, and view **Font Awesome 7 Free** families.
4. **Optional Pro:** Elementor → Settings → Integrations → Font Awesome Pro; supply your own valid Pro Kit ID, configured for Font Awesome 7 and preferably Web Fonts. Return to the Icon Library to access the Pro families.

## Compatibility

- Requires WordPress 6.2+, PHP 7.4+, and an active Elementor installation.
- Elementor Pro is **not** required for the bundled Free libraries. Availability of Elementor's Font Awesome Pro integration may depend on Elementor's installed version and plan.
- Uses Elementor's supported `elementor/icons_manager/additional_tabs` filter.
- On sites with no Kit, Free icon fonts and CSS are loaded from this plugin; Pro assets are never included.
- On sites with a Kit, icon rendering depends on the Kit actually supplying the selected styles.
- If selected Pro icons show empty squares, check the Kit's Font Awesome version, subscription, Web Fonts setting, domain restrictions, and allowed styles.
- Existing Elementor and Font Awesome 5 icons are left untouched.

## Included catalogs

- Free: Solid, Regular, Brands (catalog names derived from the official Font Awesome Free 7.3.1 distribution).
- Optional Pro: 16 Classic/Duotone/Sharp/Sharp Duotone catalogs based on Font Awesome's publicly accessible names-only 7.3.1 metadata. The public distribution contains **no Pro glyphs, Pro CSS, Pro fonts, private tokens, or Pro Kit credentials**.

## Attribution and licensing

The WordPress plugin PHP source is licensed under **GPL-2.0-or-later** (see `LICENSE`).

Bundled **Font Awesome Free** CSS, webfonts, and icon-name catalogs retain Fonticons, Inc.'s licenses:
- Free CSS and code: MIT.
- Free webfonts: SIL OFL 1.1.
- Free icons: CC BY 4.0.

See `hashe-fa7-elementor/FONT-AWESOME-LICENSE.txt` and [Font Awesome's license](https://fontawesome.com/license/free). Font Awesome and Elementor are trademarks of their respective owners. This project is an independent Hashe Computer Solutions utility and is not affiliated with or endorsed by either company.

The optional Pro picker catalogs include **icon identifiers only**, sourced from Font Awesome's public metadata endpoint. You are responsible for your Font Awesome Pro license and Kit configuration.

## Editor reliability

Version 1.0.3 fixes browser storage quota exhaustion. Elementor normally saves every normalized icon catalog into one `localStorage` entry named `elementor`; with sixteen 3,781-icon Pro catalogs, that can exceed Chrome's storage quota and leave categories blank. The editor-only guard now keeps Hashe catalogs in memory, removes only old Hashe catalog entries from Elementor storage, preserves unrelated Elementor settings, and continues to route simultaneous requests to the correct category. Normal per-category JSON fetching is preserved, with no Elementor core modifications. **v1.0.3 is undergoing logged-in editor validation on Amari before being marked stable.**

**Note:** v1.0.1 was withdrawn from the stable releases because inlining all catalog data into editor configuration caused a blank Elementor controls panel on Amari.

## Development and testing

The plugin does not create database tables or store credentials. It registers the additional tabs at runtime. The icon catalogs are static JSON and Elementor fetches only the selected tab's catalog.

If you distribute a new catalog version, update the version constant, Pro manifest, and bundled Font Awesome Free assets together. Do not commit Pro font files or private Kit material.

## Support

Community-maintained by [Hashe Computer Solutions](https://hashe.com/). Please use GitHub Issues for bugs and compatibility reports.
