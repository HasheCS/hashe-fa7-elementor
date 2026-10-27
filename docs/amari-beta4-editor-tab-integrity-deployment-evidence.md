# Amari Elementor icon picker — beta.4 targeted fix evidence (27 October 2026)

**Status:** deployed and tested on Amari development; **logged-in user's visual acceptance still pending**. This is a targeted correction, not a universal FA7 release or T01–T24 final sign-off.

## Reported failure and isolated mechanism

User's screenshot showed many indistinguishable `Font Awesome 7.3.1 Kit - Brands` entries, rather than five original native libraries + Brands + Sharp Duotone Regular.

Read-only WP-CLI editor-mode inspection of installed beta.3 reported exactly seven **unique** server tab identities: `fa-regular`, `fa-solid`, `fa-brands`, `fa-light`, `fa-duotone`, `hashe-fa7-fab`, `hashe-fa7-fasdr`. The screenshot's duplicates are thus introduced after server registration by client-side editor state and asynchronous catalog callbacks. Elementor 4.3.5's onPickerShow captures array indices and subsequently replaces `icons[index] = lib`, while the library loader retains only a shared `notifyCallback`. Both mechanics were directly inspected in Amari's installed `elementor/assets/js/editor.js`.

Beta.3 also added global native Font Awesome compatibility CSS (`.fas`, `.far`, `.fal`, `.fad`) with `!important` and substitution of Sharp Duotone for classic Light/Duotone; this was contrary to the requirement to preserve native behavior. Do not present those approximations as a fix or alter the Kit to conceal the problem.

## Applied correction

- Independent reviewed branch: `hotfix/amari-editor-tab-integrity-20261027`; candidate plugin header `1.1.0-beta.4`.
- `assets/js/editor-callback-guard.js`:
  - Captures callbacks keyed by exact library name for **both native Elementor and Hashe tabs**.
  - Avoids sending a named response with no registered owner to Elementor's global last callback.
  - Takes the authoritative PHP tab list from `window.hasheFa7ExpectedTabs` and restores that seven-entry list (native + all other legitimate editor libraries) before the picker opens, retaining its array identity while clearing the duplicated/stale entries.
  - Retains Hashe-only in-memory cache interception and never alters Elementor's native icon storage.
- `hashe-fa7-elementor.php`:
  - Outputs canonical Elementor server-registered tab metadata directly before the editor guard script through WordPress's script API; snapshots include native and any registered third-party libraries, not a hardcoded list.
  - Uses JS file modification time as the cache version.
  - Removes beta.3's global native font-family overrides entirely; no replacement Pro fonts or licensed glyphs bundled.
- **Kit `23f6a60c28` remains exactly unchanged.** Its Web Fonts selection still consists of Brands and Sharp Duotone Regular.

## PPP verification

1. **Full plugin backup** stored privately at `/bigdisk/home/devsasseagency/.hashe-fa7-backups/amari-pre-beta4-20261027T080211Z-plugin.tar.gz`, 546 KiB, tar verified.
2. **Full WordPress DB backup** `/bigdisk/home/devsasseagency/.hashe-fa7-backups/amari-pre-beta4-20261027T080211Z-db.sql.gz`, 4.9 MiB, gzip integrity checked.
3. Prior installed beta.3 SHA-256 pinned (main PHP `8d55b899fcef12bcb54d3d6076c08a8b6b219aa5eecfd68d97f6e930dc45687e`; JS `bbee217d62d66786ac912728d6c1b11dc1207a36c78d25595da60615e99a2fc8`).
4. Candidate checked out into private `/tmp` stage and passed `php -l`, PHP smoke tests, and asynchronous JS regression tests. Tests reproduce **twelve fake repeated Brands** alongside the native categories and ensure exactly five native + two correctly named Kit tabs after delayed completion, protecting native storage.
5. **Actual Amari WordPress + Elementor/Pro canary with Hashe skipped and candidate loaded:** exactly seven server tabs and exactly seven uniquely named JSON descriptors injected into the registered WP script; selected Kit unchanged. The canary was also run **after deployment** with the installed beta.4 and returned:
   - `SERVER_TABS=fa-regular,fa-solid,fa-brands,fa-light,fa-duotone,hashe-fa7-fab,hashe-fa7-fasdr`
   - `EDITOR_BASELINE=7 unique, Kit unchanged, native classes untouched`
   - `GUARD_VERSION=1793088235`.
6. Applied only vetted main PHP and editor-guard JS files to **Amari development**; PHP lint clean; WP-CLI reports version `1.1.0-beta.4`, Kit `23f6a60c28`.
7. LiteSpeed purge for this **single-site** WordPress install: success. Public HTML after purge does not include obsolete `Hashe Elementor Native Solid` inline CSS; main PHP has no native-compat enqueue function. Public guard JS returned HTTP 200, 7,243 bytes.
8. **Actual headless Chromium** running `tests/browser-picker-integrity.html` returned `<body data-fa7-qa="pass">` with exactly seven correct category labels and asynchronous completion. The smoke workflow has since included this real Chromium DOM check and passed on GitHub Actions.
9. GitHub PHP/Node/Chromium workflow at branch HEAD `ecdbd489cfae66d2c09604eb733a23a23f6323f5` passed (PHP matrix 7.4/8.2/8.3).

## Limits and rollback

- **No authenticated screenshot of the actual Elementor popup after beta.4** is available from this run. The headless Chromium test runs the isolated DOM reproduction with code matching Elementor's callback pattern; it is not proof of a user's active Elementor editor. Do not mark the real logged-in editor acceptance test passed solely from this.
- SVG, By Icon, Pro Plus, true Pro family rendering and complete catalog update requirements remain unresolved on the larger engineering branch. Public `main` and stable `v1.0.3` were not changed.
- To restore the pre-beta.4 plugin if needed, use the full verified private plugin archive above, restore to `$site/wp-content/plugins`, then purge Amari site cache. The DB backup is an independent safeguard, not necessary for a two-file code rollback.
