# Amari restricted-Kit native Elementor icon compatibility — installed beta.3

Date: **2026-10-27** · Status: **Deployed to Amari development site for user acceptance, not public release**.

## Target and unchanged constraints
- Amari: `https://dev.sasseagency.com/amari/`.
- Actual installed versions: Elementor **4.3.5**, Elementor Pro **4.3.2**, Hashe **1.1.0-beta.3**.
- Elementor Kit `23f6a60c28` **unchanged**; Web Fonts with JavaScript embed, Kit config `method=css`.
- Only Kit-selected styles are **Brands** and **Sharp Duotone Regular**.
- Elementor continues to register its original five tabs; Hashe adds exactly two selected Kit tabs.
- No extra Font Awesome credentials, account changes, Pro asset redistribution or modification to saved icon values.

## Root cause and deliberately limited correction
- Elementor Pro inserts five native Pro tabs whenever a Kit ID is configured, even if that Kit does **not** provide those families. Native tab `url`/`enqueue` fields are `false` and depend on the Kit to load fonts. Five native tabs are identical in read-only live WP-CLI editor-mode A/B with Hashe loaded and skipped.
- The selected two-style Kit lacks the classic Pro Solid, Regular, Light and Duotone font faces; its Brands face is present.
- This **compatibility fallback** keeps the seven tabs and same Kit. For native `fas` and `far`, use the plugin's existing licensed FA7 Free font faces and fall back to the Kit's Sharp Duotone font if a glyph is absent from Free. Native `fab` continues using Kit Brands.
- For missing native `fal`/Light and `fad`/Classic Duotone, use **Kit Sharp Duotone as a visual fallback** and suppress the native Duotone's second pseudo-element to avoid double glyphs. **Important: these are visible fallback glyphs, NOT authentic Classic Light or Classic Duotone styles.** The customer's restricted Kit cannot provide those exact unselected Pro font assets. Do not label this fallback as full style support.
- No registered native tab metadata, Kit styles, Kit settings or saved Elementor icon class/value is rewritten. CSS is conditional, only for families absent from the detected Kit, and disabled for SVG/JS Kits.

## Verified PPP steps
1. Full original plugin directory compressed backup and full MySQL WordPress database backup saved **outside web root** in the site's private owner backup directory, with non-empty files and verified archive. Rollback kept.
2. Compared live installed beta.2 main file with source beta.2. It had one local editor-cache-busting `filemtime` patch; retained exactly in beta.3.
3. Constructed hotfix as **minimal changes** to the installed beta.2, not replacement with the unfinished integrated engineering branch. New stylesheet and appended native compatibility functions only.
4. PHP lint clean. WP-CLI staged plugin loaded within Amari real Elementor/Pro: **5 native + 2 Hashe tabs**, 4 conditional native fallback rules, appropriate Duotone pseudo-element suppression, unchanged Kit ID.
5. Atomically promoted validated PHP main file and added stylesheet to installed Amari plugin. Live WP-CLI plugin version **1.1.0-beta.3**; live editor-mode hooks: same seven tabs, CSS attached, Kit ID unchanged.
6. Real headless Chromium rendering **after deployment** on uncached Amari public page: actual plugin CSS inline present, and `fas fa-house`, `far fa-house`, `fal fa-house`, `fad fa-house`, `fab fa-github`, `fa-sharp-duotone fa-regular fa-house` displayed recognizable glyphs. No separate double Duotone glyph after the corrective `::after{content:none;display:none}`.
7. LiteSpeed Cache purge succeeded for Amari **single site** only. Public endpoint HTTP 200; compatibility CSS HTTP 200.
8. Exact installed beta.3 main PHP and CSS matched the dedicated GitHub hotfix branch byte-for-byte (SHA256 PHP `8d55b899fcef12bcb54d3d6076c08a8b6b219aa5eecfd68d97f6e930dc45687e`, CSS `090022a826ce77e9e435c4ed12616e0d3338d009a278a0bbbef38c19c742af9b`).

## Open gates
- User final verification inside **actual logged-in Elementor icon picker** across five native tabs, both Hashe tabs, selected icon saving, editor and frontend.
- Representative icons render in the public browser but **not every Pro-only icon** has been individually validated. Fallback style differences remain inherent to the restricted Kit.
- SVG / By Icon / Pro Plus universal feature integration and full T01–T24 acceptance remain separate, incomplete engineering work; beta.3 is NOT a new public release or universal plugin signoff.
- Main branch and existing public release `v1.0.3` remain unchanged; no CEO work-ledger update before project completion.
