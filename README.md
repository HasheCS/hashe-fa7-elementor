# Hashe Font Awesome 7 Icon Library for Elementor

An independently developed WordPress plugin that adds Font Awesome 7 styles to the **Elementor Icon Library**, including Pro Plus styles available in a customer's own Font Awesome Kit.

**Stable v1.1.2 | Accepted on Solutions playground, October 31, 2026.**

## What it does

- **No Kit:** Elementor retains its native Free Regular, Solid and Brands libraries. Separate Font Awesome 7 Free libraries appear under **My Libraries**.
- **Selected-style Kit:** Elementor's three native baseline libraries remain available, using the original Elementor Free fonts where corresponding Pro families are not in the Kit. Native Light and Duotone appear only when their styles are available. Selected additional families (such as Jelly, Notdog and Slab Duo) appear under **My Libraries**.
- **Whole-library Kit:** Elementor owns its five native Pro categories. The plugin adds the remaining supported Font Awesome 7 styles under **My Libraries**, including Pro Plus families, without duplicating native tabs.

A Kit's **published CSS/JS assets** determine its actual style entitlement. Family-style names and available collections are discovered dynamically using Font Awesome's release-specific **public metadata REST API**. The plugin no longer relies on a fixed 17-style catalog. The icon catalog is cached on the WordPress site with a last-successful fallback; Kit revisions are checked on editor load.

The integration never copies or redistributes paid Font Awesome fonts, SVGs, paid stylesheets or bundled Pro metadata catalogs. It does not request omitted paid font shards. The licensed Kit supplies all paid glyphs.

## Installation

1. Back up the WordPress site and installed plugin.
2. Download the **installable `hashe-fa7-elementor-v1.1.2.zip` release asset** from [GitHub Releases](https://github.com/HasheCS/hashe-fa7-elementor/releases/tag/v1.1.2). Do not install GitHub's automatically generated source ZIP.
3. WordPress: **Plugins > Add New Plugin > Upload Plugin**. Install/replace and activate.
4. For Free use, leave the Kit field in Elementor Pro integrations empty. To use Pro/Pro Plus, add your own licensed Font Awesome Kit ID.
5. Reopen or hard-refresh Elementor after changes. Test selection, saving and frontend rendering.

## Verification

- **Owner visual acceptance (October 31, 2026):** no Kit, selected-style Pro Plus Kit and whole-library Pro Plus Kit. Icon selection and saving were confirmed by the owner.
- **Isolated WordPress QA:** four Kit modes with editor and frontend regression tests, including native definitions, expected additional libraries and original Free fallback fonts.
- **Isolated Chrome:** verified Free Solid, Free Regular, Free Brands, Jelly and Notdog rendering together on the Solutions playground without manual CSS injection.
- GitHub Actions validates PHP 7.4, 8.2 and 8.3 syntax and regression scenarios, and JavaScript memory-adapter tests. Workflow results for this release should be checked separately.

The stable release changes only version markers and documentation relative to the accepted `1.1.2-rc.3` runtime. No clean third-party-site deployment or official Font Awesome endorsement is claimed.

## Licensing

Plugin integration code: GPL-2.0-or-later. Bundled original Font Awesome Free assets retain their own applicable SIL OFL 1.1, CC BY 4.0 and MIT terms; see [bundled license notice](hashe-fa7-elementor/FONT-AWESOME-LICENSE.txt).

Font Awesome and Elementor are third-party trademarks; this plugin is not an official product of either company.
