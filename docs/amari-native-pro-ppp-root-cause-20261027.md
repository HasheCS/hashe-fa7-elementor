# Amari native Font Awesome Pro icon libraries: PPP root-cause evidence
Date: 2026-10-27
Status: **Root cause established at Elementor registration + hosted-font level. Real logged-in icon-picker glyph comparison remains unexecuted.**

## Real installation

- Site: `https://dev.sasseagency.com/amari/` (development).
- Read-only, key-verified SSH via `sasse` alias → WordPress CLI in `/bigdisk/home/devsasseagency/public_html/amari`.
- Elementor 4.3.5 active; Elementor Pro 4.3.2 active; Hashe 1.1.0-beta.2 active; LiteSpeed Cache 7.9.2 active.
- Elementor Kit setting: `elementor_font_awesome_pro_kit_id=23f6a60c28`.
- Read-only WP-CLI editor-mode emulation: `\\Elementor\\Plugin::$instance->editor->set_edit_mode(true)`, then `\\Elementor\\Icons_Manager::get_icon_manager_tabs()`. The test ran in two independent PHP processes, second with WP-CLI `--skip-plugins=hashe-fa7-elementor`. No plugin deactivation, Kit edit, database configuration change, or Amari deployment.
- Non-sensitive PHP probe script was temporarily placed outside WordPress root under `/tmp`, then removed.

## Exact read-only A/B results

| Native library | With Hashe | With Hashe skipped | `url` in both |
| --- | --- | --- | --- |
| `fa-regular` (Regular Pro) | Present | Present | false |
| `fa-solid` (Solid Pro) | Present | Present | false |
| `fa-brands` (Brands Pro) | Present | Present | false |
| `fa-light` (Light Pro) | Present | Present | false |
| `fa-duotone` (Duotone Pro) | Present | Present | false |

`TAB_COUNT=7` with Hashe: those five + `hashe-fa7-fab` (Brands) + `hashe-fa7-fasdr` (Sharp Duotone Regular).
`TAB_COUNT=5` without Hashe: exactly the five native Pro tabs.
All native tab display prefixes, stylesheet URLs, and other displayed settings were identical.

**Important context:** An ordinary WP-CLI frontend-context call yielded zero native tabs because Amari's Elementor Inline Font Icons feature (`e_font_icon_svg`) is active. The test explicitly enabled Elementor's editor-mode flag in memory to obtain the relevant *icon picker* tabs. Do not misinterpret the frontend-context result as a Hashe regression.

## Elementor Pro's own native source explains why

Read-only inspected live site file:
`wp-content/plugins/elementor-pro/modules/assets-manager/asset-types/icons/font-awesome-pro.php`

1. When a Font Awesome Kit ID is set, Elementor Pro attaches `replace_font_awesome_pro()` to `elementor/icons_manager/native`.
2. It **replaces** Free Solid / Regular / Brands entries with five Pro entries (`far`, `fas`, `fab`, `fal`, `fad`), even if the Kit does not contain those five styles.
3. Each Pro entry has `'url' => false` and `'enqueue' => false`; Elementor does not register five separate font files through those tabs.
4. Elementor Pro enqueues `https://kit.fontawesome.com/{kit_id}.js` using `enqueue_kit_js()` for its editor and frontend. This is **Elementor Pro behavior, not Hashe's filter**.

## Hosted two-style Kit asset verification

Read-only obtained `https://ka-p.fontawesome.com/assets/23f6a60c28/112386267/kit.css?token=23f6a60c28` (HTTP 200, 184,544 bytes).
- Enabled actual font faces: Font Awesome 7 **Brands/400**, Font Awesome 7 **Sharp Duotone/400** only.
- **No Classic Regular/400, Solid/900, Light/300 or Classic Duotone/900 font faces** in this Kit.
- Kit CSS does include both `.fab` and `.fa-brands` class selectors and Brand icon mapping such as `.fa-github`, so the native **Brands** group has legitimate Kit font resources. Do not imply all five must fail; user screenshots specifically confirmed empty Light/Duotone glyphs and further native browser behavior remains to be verified.
- Kit's JS embed vs `method=css` is correct: `css` indicates Web Fonts technology, JS is embed mechanism.

## Root-cause disposition

The **native-tab presence/configuration issue is not caused by Hashe**: the five registered entries are unchanged with Hashe skipped, and are injected by Elementor Pro itself.
The **missing Light/Duotone and other unselected Pro glyph resources** are a predictable consequence of the customer's two-style Kit configuration; the Kit physically does not furnish these fonts. This is consistent with Font Awesome's public subset documentation.

This evidence strongly identifies the configured Kit and Elementor's native Pro behavior as the cause of missing glyphs, but **does not substitute for a logged-in browser enabled/disabled comparison of rendered individual tiles**. T04's browser-glyph gate remains pending.

**No legitimate plugin-only way to render paid Classic styles that are absent from the Kit** while preserving the single Kit ID and no paid redistribution rule. The supported user action for all five native Pro styles to render is selecting the necessary Classic Pro styles in the Kit (or using a full-library Kit already licensed by the customer), after explicit approval; don't silently change their Kit configuration. That will naturally affect Hashe's additional-tab list because it is designed to reflect Kit selection.

## Next steps under PPP

1. Present this root-cause distinction to Mamoon, not another speculative CSS/label ZIP.
2. Do not modify native Elementor tabs or load unauthorized paid fonts.
3. If the user elects to enable Classic styles in the Kit, repeat logged-in browser acceptance of all five Pro tabs and all Hashe tabs, including saved widgets and frontend.
4. Maintain separate engineering work for exact SVG/By Icon discovery and complete release acceptance. Do not mark a partially tested beta complete.

## Supplemental finding and deployed native callback hotfix (27 October 2026)

**Prior root-cause assessment above is incomplete for the reported regression.** The fact that some Kit font faces are not selected cannot explain why an Elementor-native catalog's asynchronous result is delivered to the wrong picker tab. This newer, direct inspection of the actual installed Elementor editor source identifies an independent plugin-side callback problem.

The installed Elementor 4.3.5 JS at `wp-content/plugins/elementor/assets/js/editor.js` implements `iconManager.library.initIconType(libraryConfig, callback)` by assigning **one shared mutable** `_this.notifyCallback = callback`. The asynchronous fetch invokes `normalizeIconList`, then `this.runCallback(library)`; without per-library routing, whichever native/Hashe request initialized last can own that callback. Original Hashe beta.2 guard intercepted only `hashe-fa7-` callbacks; a native response could therefore invoke an unrelated callback while multiple categories were being loaded.

**Code hotfix:** `assets/js/editor-callback-guard.js` now associates callbacks with **every** library's exact `settings.name` (native and Hashe) while restricting localStorage interception to Hashe only. `hashe-fa7-elementor.php` now versions the editor script by its file modification time instead of reusing a stale beta version URL. Existing Kit discovery, Kit settings, native icons, native CSS, Elementor Pro, and frontend logic are unmodified.

**Regression fixture:** The Node editor test now initializes all five native Pro libraries plus additional Hashe libraries concurrently, with intentionally interleaved response delays, and verifies each callback returns to its owning tab once. All PHP 7.4 / 8.2 / 8.3 editor and PHP smoke regression checks passed in the isolated engineering branch.

**Applied to Amari, safely:**
- Baseline compared byte-for-byte to the pinned previous beta repository revision `cb8cad42da7bc5457a5524e9554ff1f004d092fa` **before modification**.
- Full installed plugin backed up and tar verified at `/bigdisk/home/devsasseagency/.hashe-fa7-backups/amari-native-icon-callback-20261027T042317Z.tar.gz`. This is a private backup location, **not** a download or public file link.
- Only the guard JS and the main plugin PHP enqueue version were changed in place; no database changes, plugin deactivation, Kit changes, WordPress admin UI changes, or site-wide cache purge.
- PHP lint passed; checksummed new guard: `bbee217d62d66786ac912728d6c1b11dc1207a36c78d25595da60615e99a2fc8`.
- WP-CLI editor-mode post-deployment: `NATIVE_TABS=5`, `HASHE_TABS=2`, `KIT=23f6a60c28`, `GUARD_VERSION=GUARD_MTIME=1793074997`.
- Public static JS URL `https://dev.sasseagency.com/amari/wp-content/plugins/hashe-fa7-elementor/assets/js/editor-callback-guard.js?ver=1793074997` returned HTTP 200, 5,139 bytes and contains the name-based routing fix.
- Amari still reports plugin beta.2 because this was a targeted installed-version hotfix, **not** a release or promotion of incomplete integration work. It is not evidence that the entire development branch was deployed.

**Not yet verified:** A signed-in editor visual test must confirm that each of the five original Elementor categories displays its own icons, not blanks, after the JavaScript hotfix. The missing-font concern for styles absent from the Kit is separate and must not be represented as the sole regression cause. Do not mark T04 fully passed until visual and saved-widget checks are completed.
