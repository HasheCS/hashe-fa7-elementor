# Hashe Font Awesome 7 Icon Library for Elementor

An independent GNU GPL WordPress plugin for extending **Elementor's existing Icon Library** with Font Awesome icons without replacing Elementor Core or Pro definitions.

## v1.1.1 candidate — licensing-conscious architecture

**QA14 / v1.1.1-rc.3:** A small CSS Kit containing only Sharp styles can now render Elementor's unchanged native Classic/Duotone tab icons using already-licensed Sharp fonts. This is a *Sharp visual fallback*, not authentic Classic or two-color Duotone rendering. No omitted Pro fonts are requested. The no-Kit Free and full-Kit modes remain unchanged.

This is a **development candidate pending the Solutions Moco owner’s visual acceptance**, not a released version. The current public stable release remains [v1.1.0](https://github.com/HasheCS/hashe-fa7-elementor/releases/tag/v1.1.0).

### How it works

- **No Kit ID configured:** Three additional libraries—Font Awesome Free Solid, Regular and Brands—appear under **My Libraries**. All fonts and stylesheets come from the included original Font Awesome **Free 7.3.1** files, with applicable license notices. No account, network metadata call or paid entitlement is required.
- **Supported licensed Kit configured:** Reads the customer's Kit ID from Elementor's normal integration setting. Determines the Kit's actual selected CSS font styles, or strictly verifies the full-Pro JavaScript startup. Only verified supported styles become extra libraries. No Kit ID is hardcoded.
- **Version-aware names:** Gets names and Free/Pro family-style metadata from the [official Font Awesome public release-specific REST API](https://docs.fontawesome.com/apis/rest/). Version 7.x with supported prefixes is recognized; an unknown major version fails safely. No copied Pro name datasets are shipped.
- **Caching:** Downloads public metadata only on a cold editor load or refresh, then caches the successful result on that WordPress site for seven days; a validated last-known-successful copy can be reused during an API outage for up to 30 days. Fresh first-run download may take several seconds. It does not run on every frontend page request.
- **Rendering and licensing:** Free mode uses the original bundled Font Awesome Free assets. Paid Kit mode delegates icons/fonts entirely to the **customer's configured Kit**. It never ships paid fonts, paid SVGs or paid CSS/JS, does not use developer credentials, and never constructs direct paid font-shard requests for unselected styles.
- **Native Elementor libraries:** The plugin never filters or replaces the five native Core/Pro tab definitions. Under a subsetted Kit, however, icons in native Elementor Pro tabs for styles **omitted from that Kit** may be unavailable because Elementor Pro itself suppresses its default native font loading. We do not bypass Kit selections to fill these omissions. This is a known visual regression risk requiring explicit review in the candidate.
- **Performance:** The original Kit-only editor-memory adapter prevents localStorage quota errors with a full 17-style Kit. It does not modify native Elementor caches.

### Requirements and scope

- WordPress 6.2+, PHP 7.4+, Elementor active.
- Verified earlier: Elementor 4.3.5, Elementor Pro 4.3.2, Font Awesome 7.3.1 on Solutions Moco.
- Pro+ exclusive styles, arbitrary subsetted SVG+JS Kits, all future major versions and clean-install browser QA are **not verified**. Do not advertise them as tested.
- If an invalid Kit ID is configured, the plugin does not fabricate Kit libraries or inject Free CSS into Elementor Pro's own Kit integration. Clear the Kit ID to use no-Kit Free mode.
- Metadata is informational, not a license. Kit availability and the customer's subscription determine usable styles.

### Development and release validation

The candidate must pass PHP and JavaScript regressions, public metadata API/caching tests, small/full/no Kit tests, native icon browser inspection, frontend saved-icon rendering, and explicit owner visual acceptance before any merge, tag or public release.

### Third-party licensing

Own integration code: GPL-2.0-or-later. Font Awesome Free assets: original MIT, SIL OFL and CC BY terms as applicable; see [bundled notice](hashe-fa7-elementor/FONT-AWESOME-LICENSE.txt). Font Awesome and Elementor are third-party trademarks; the plugin is not endorsed by either company.

**Do not install GitHub's repository source-code ZIP as a WordPress plugin.** Once a release is approved, use its separate installable plugin ZIP asset.
