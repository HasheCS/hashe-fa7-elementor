# Hashe Font Awesome 7 Icon Library for Elementor

An independent WordPress plugin from [Hashe Computer Solutions](https://hashe.com/) that adds searchable **Font Awesome 7** catalogs to **Elementor's existing Icon Library**. It does not modify Elementor core files.

**Recommended version: [v1.0.3](https://github.com/HasheCS/hashe-fa7-elementor/releases/tag/v1.0.3)** · **[Download the WordPress-installable ZIP](https://github.com/HasheCS/hashe-fa7-elementor/releases/download/v1.0.3/hashe-fa7-elementor-v1.0.3.zip)**

> **Download the attached release ZIP, not GitHub's "Code → Download ZIP".** GitHub's source ZIP contains repository files and is not packaged for direct installation as a WordPress plugin. For future versions, use the ZIP attachment matching the version on the [latest release page](https://github.com/HasheCS/hashe-fa7-elementor/releases/latest).

## Requirements and compatibility

- WordPress **6.2 or later**, PHP **7.4 or later**, and the **Elementor** plugin installed and active.
- Elementor Pro is **not required** to use the bundled Free catalogs.
- A Font Awesome Pro subscription is **not included**. Optional Pro rendering requires an **existing, compatible, licensed Font Awesome Kit**, configured in Elementor.
- Verified for the specific Amari installation described under [Testing and limitations](#testing-and-limitations). Compatibility across all WordPress, Elementor, PHP, themes, other plugins, and browsers is **not guaranteed**.

## What the plugin adds

| Catalogs | Kit required? | What is included |
| --- | --- | --- |
| Font Awesome 7.3.1 **Free Solid, Regular and Brands** | **No** | Three searchable icon catalogs, plus licensed Free CSS and webfonts |
| **16 additional Pro style catalogs** (Classic, Duotone, Sharp, and Sharp Duotone variations) | **Yes, for display and licensed rendering** | Publicly available **icon names only**; **no** Pro CSS, webfonts, glyphs, SVGs, license, or credentials |

Existing Elementor icon tabs and saved icons are preserved.

**How Kit integration works:** With no Kit ID set, the plugin serves its bundled Font Awesome Free styles and fonts. If a Kit ID is configured, the plugin leaves font loading to the site's Kit, including for Free styles. Therefore, a missing, incompatible, misconfigured, or unlicensed Kit can result in icons appearing in the picker but **not rendering**. Merely entering a Kit ID does **not** unlock Font Awesome Pro.

## Installation

1. Open [v1.0.3 on GitHub Releases](https://github.com/HasheCS/hashe-fa7-elementor/releases/tag/v1.0.3) and download the attached **`hashe-fa7-elementor-v1.0.3.zip`** file (or the matching attachment from a newer recommended release).
2. In WordPress, select **Plugins → Add New Plugin → Upload Plugin**, choose that ZIP, then install and activate it. Ensure Elementor itself is active.
3. Edit a page with Elementor. Open a widget's Icon Library control and look for **Font Awesome 7 Free — Solid, Regular, Brands**.
4. **Optional Pro:** If you already have a licensed Font Awesome Kit, enter its Kit ID under Elementor's **Settings → Integrations → Font Awesome Pro** (location and availability depend on the installed Elementor version). Ensure your Kit is configured to provide the intended Font Awesome 7 families/styles.

**Upgrading:** Back up the site before replacing a plugin version, especially on production sites. Use a versioned **release attachment** and verify its version after installation. If your Elementor editor is affected, revert using a previously tested site backup; older release ZIPs are not guaranteed to resolve every configuration issue.

## Troubleshooting

| Symptom | What to check |
| --- | --- |
| Font Awesome 7 tabs are missing | Confirm Elementor and this plugin are active; refresh the Elementor editor; check plugin requirements and browser console for errors |
| Pro tabs are missing | Confirm Elementor has a Kit ID configured; the Pro tabs are conditional on that setting |
| Pro icon names appear but glyphs are blank or show squares | Confirm your Kit's **actual Pro entitlement**, Font Awesome version, enabled styles, Web Fonts mode, domain restrictions, and network loading |
| Free icons do not render after adding a Kit | Confirm the Kit serves the relevant Free families, or remove the Kit configuration if you only need the bundled Free icons |
| Elementor editor or picker is blank | Install the recommended **v1.0.3** release; check browser console errors and conflicts; report reproducible details in [GitHub Issues](https://github.com/HasheCS/hashe-fa7-elementor/issues) |

When reporting a bug, include WordPress, PHP, Elementor and plugin versions, relevant browser details, steps to reproduce, and sanitized error messages. **Do not post Kit credentials, private tokens or personal data publicly.**

## Release status

| Version | Public status | Guidance |
| --- | --- | --- |
| [v1.0.3](https://github.com/HasheCS/hashe-fa7-elementor/releases/tag/v1.0.3) | **Current recommended release** | Addresses Elementor icon-library browser storage quota errors |
| [v1.0.0](https://github.com/HasheCS/hashe-fa7-elementor/releases/tag/v1.0.0) | **Superseded historical baseline** | Retained for reference; not recommended for new installs or as a universally safe rollback |
| `v1.0.1` | **Withdrawn / known defective** | Caused an Elementor editor regression. Its public release installer was removed. **Do not install.** The Git tag and commits remain for debugging and traceability |

Version **1.0.2** was a development candidate, **not a separately published GitHub release**.

## Testing and limitations

**v1.0.3** uses an editor-only callback/storage guard to prevent Elementor's large icon catalogs from exhausting browser `localStorage`. It retains per-category JSON loading and avoids intentionally deleting unrelated Elementor storage.

- The repository's [GitHub Actions workflow](https://github.com/HasheCS/hashe-fa7-elementor/actions) runs **PHP lint/smoke tests** on PHP 7.4, 8.2 and 8.3, plus a **Node.js simulated editor-storage/callback regression test**. These automated checks are **not** a complete live browser or WordPress compatibility suite.
- [Pull request #1](https://github.com/HasheCS/hashe-fa7-elementor/pull/1) records additional Chromium-based reproduction/testing and a confirmed Amari deployment. On **26 October 2026**, the Amari site owner confirmed the Elementor editor was working after installing v1.0.3.
- Other sites, Elementor versions, Kits, browsers, and combinations have **not** been exhaustively tested. Please use [GitHub Issues](https://github.com/HasheCS/hashe-fa7-elementor/issues) for reproducible compatibility reports.

The plugin stores no Pro API keys and creates no WordPress database tables. Pro style catalogs contain names only; rendering is controlled by the external licensed Kit.

## Licenses and attribution

- Hashe's plugin PHP code: **GPL-2.0-or-later**, see [LICENSE](LICENSE).
- Bundled **Font Awesome Free**: CSS/code under **MIT**, webfonts under **SIL OFL 1.1**, and icons under **CC BY 4.0**, as applicable. See [the bundled Font Awesome license notices](hashe-fa7-elementor/FONT-AWESOME-LICENSE.txt) and [Font Awesome's licensing terms](https://fontawesome.com/license/free).
- Optional Pro icon names are derived from Font Awesome's publicly accessible metadata. This plugin does **not** provide or bypass any Font Awesome Pro license.
- Font Awesome, Elementor, and referenced brand icons remain trademarks of their respective owners. This project is **not affiliated with or endorsed by Font Awesome or Elementor**.

## Development and support

The plugin registers tabs using Elementor's `elementor/icons_manager/additional_tabs` filter without patching Elementor core. Source and tests are included in this repository; the [CI workflow](.github/workflows/php-smoke.yml) documents automated checks.

For regular defects and compatibility reports, use [GitHub Issues](https://github.com/HasheCS/hashe-fa7-elementor/issues). The project is published by [Hashe Computer Solutions](https://hashe.com/).
