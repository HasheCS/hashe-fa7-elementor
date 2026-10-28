# Hashe Font Awesome 7 Icon Library for Elementor

A WordPress plugin from Hashe Computer Solutions that **extends Elementor's existing icon picker** with the styles actually published by the user's configured Font Awesome Kit.

**Current release: [v1.1.0](https://github.com/HasheCS/hashe-fa7-elementor/releases/tag/v1.1.0)**. Install the attached **hashe-fa7-elementor-v1.1.0.zip** from GitHub Releases. **Do not use GitHub's automatically generated source-code ZIP** as the WordPress installer.

## Compatibility and requirements

- WordPress **6.2+**, PHP **7.4+**, Elementor active.
- This Kit-extension integration was verified with **Elementor Core 4.3.5**, **Elementor Pro 4.3.2**, and **Font Awesome Pro 7.3.1**.
- Configure a compatible licensed Kit ID through **Elementor > Settings > Integrations > Font Awesome Pro** (menu location depends on Elementor version). The plugin does **not** provide a Font Awesome subscription, paid font files, or rights to paid icons.
- Both subsetted **Web Fonts/CSS Kits** and a verified **full Pro SVG+JS Kit** were tested. The full Kit showed 17 standard styles; styles are not unconditionally enabled by hardcoded Kit IDs.
- **Pro+ exclusive styles and future Font Awesome versions are not yet verified or advertised as supported.** Other Elementor/WordPress combinations are not guaranteed.

## What it does

1. Reads the Kit ID from Elementor's existing settings. No Kit ID is hardcoded.
2. Inspects the published Kit configuration. With a CSS Kit, detects standard styles from the actual published Kit font files; with the verified full Pro JS Kit, checks the expected official startup asset.
3. Adds only those supported styles as **custom libraries below "My Libraries"** in Elementor's native icon picker.
4. Leaves **all five Elementor Core/Pro native libraries above "My Libraries"** exactly as Elementor defines them, without replacing or filtering the native tab definitions.
5. Rechecks the active Kit revision on editor initialization, so adding/removing styles or switching Kits updates the list after reopening the editor.
6. Uses a narrowly scoped editor-memory adapter for larger Kits; only the plugin's own Kit icon-catalog storage keys are handled in memory, preserving native Elementor library storage and other settings.
7. When a CSS Kit omits Fonts Awesome Brands, loads the **unmodified Font Awesome Free Brands 7.3.1 asset** to satisfy the native Brands icons used by Elementor's sidebar labels.
8. For select original Elementor native Pro icon families absent from a subsetted CSS Kit, the existing compatibility layer requests corresponding font data **from the user's configured licensed Kit**. It does not bundle paid font files.

The plugin uses Font Awesome **7.3.1 names-only metadata** to build searchable catalogs. It cannot grant access to styles missing from the user's subscription or Kit.

## Installation

1. Back up the WordPress site, particularly before upgrading an existing icon plugin.
2. Download **hashe-fa7-elementor-v1.1.0.zip** from the [v1.1.0 release](https://github.com/HasheCS/hashe-fa7-elementor/releases/tag/v1.1.0).
3. In WordPress, open **Plugins > Add New Plugin > Upload Plugin**, select that ZIP, install, and activate. Elementor must be active.
4. In Elementor Pro's integration settings, configure your own compatible licensed Font Awesome Kit ID.
5. Reopen the Elementor editor, open an icon control, and check the additional styles **below My Libraries**. Native Elementor libraries should remain above the separator.

**If no supported Kit is configured, no custom Kit libraries are added.** The plugin does not replace Elementor's existing Free/Pro icon libraries.

## Validation and limitations

v1.1.0 was visually accepted on the **Solutions Moco development site on 2026-10-28**: native libraries and labels, changing Kit style selections from one to three, full 17-style Pro Kit, and large catalog storage. The release CI checks PHP syntax and mock-backed Kit/native/asset regressions, plus scoped JavaScript storage behavior. Those tests do **not** establish compatibility for Pro+ subscriptions or every WordPress environment.

The public repository's prior **v1.0.3** release is historical and has a different architecture. Versions **v1.0.0** and **v1.0.1** are not recommended as a blanket fallback; v1.0.1 was withdrawn after an editor regression.

## Troubleshooting

- **No added libraries:** Check Elementor Pro Kit ID, supported Font Awesome version, and actual Kit styles. Reopen the editor after publishing Kit changes.
- **Missing glyphs:** Check Kit subscription, enabled families, Kit method, site domain restrictions, and network access to the official Font Awesome CDN.
- **Editor storage errors:** Verify this version and confirm the browser has loaded the current editor JS.
- **Regression:** Restore from the site's own pre-upgrade backup, and report the Elementor/PHP/plugin versions plus sanitized browser-console evidence. Do not publish private access credentials.

## Licensing

- Hashe-owned plugin PHP and JavaScript: **GPL-2.0-or-later** (see [LICENSE](LICENSE)).
- Bundled **Font Awesome Free** stylesheets and webfonts remain under their original **MIT/SIL OFL** terms (see [FONT-AWESOME-LICENSE.txt](hashe-fa7-elementor/FONT-AWESOME-LICENSE.txt)).
- Font Awesome icon-name catalogs are **metadata only**; no paid Font Awesome Pro glyphs, paid fonts, paid CSS/JS distributions, or entitlement credentials are included. Using the user's Kit requires their own applicable Font Awesome license.
- Font Awesome and Elementor are trademarks of their respective owners. This project is independently developed and is not endorsed by either company.

## Contributing and future features

File reproducible issues in [GitHub Issues](https://github.com/HasheCS/hashe-fa7-elementor/issues). Development should proceed in branches and pass regression testing before merging. **Pro+ compatibility is a future feature**, not part of v1.1.0.
