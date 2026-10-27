# Amari Elementor Icon Library — PPP corrective audit (27 October 2026)

**Status: failed user acceptance; freeze new live hotfixes.** This document supersedes the earlier "native categories fixed" conclusion for the deployed site. Not a release or request to change the Kit.

## Immutable requirements rechecked against approved blueprint
Source: `HASHE_FA7_ELEMENTOR_COMPLETE_ENGINEERING_BLUEPRINT_2026-10-27.md`, sections 1–10, specifically W1/W3/W4/W6 and T02/T04/T05/T16/T19/T21/T22.
1. Keep existing Elementor Kit `23f6a60c28` unmodified (Web Fonts with JS embed; selected Brands and Sharp Duotone Regular).
2. Exactly **two** Hashe categories, each with its proper name/icons, independently of five genuine native Elementor Pro categories.
3. Do not remove, duplicate, spoof, hijack, or globally restyle native Elementor libraries; avoid `!important` selectors for legacy `.fas/.far/.fal/.fad` by default.
4. No paid Pro font redistribution and no misleading approximations presented as genuine Classic styles.
5. No site hotfix without repository-based review, real editor/browser QA, backup and explicit controlled deployment. No "PHP tests pass therefore editor fixed" reasoning.

## Evidence inventory and findings
- User supplied Amari Icon Library screenshot (1264×880) in ChatGPT, October 27; **My Libraries** contains around ten indistinguishable **Font Awesome 7.3.1 Kit - Brands** entries instead of Brands + Sharp Duotone Regular. There is real Brands artwork in the right-hand grid, so neither total Font Awesome CDN failure nor universally blank icon tiles explains this screenshot.
- Read-only SSH + WP-CLI on **currently installed beta.3** (Elementor 4.3.5, Elementor Pro 4.3.2) returned exactly **7 uniquely named tabs**: `fa-regular`, `fa-solid`, `fa-brands`, `fa-light`, `fa-duotone`, `hashe-fa7-fab`, `hashe-fa7-fasdr`. Selected Kit unchanged. **No duplicate Brands server-side.**
- **Consequence:** The duplicate tab list is being introduced or retained in the browser-side Elementor editor, its serialized initial config/cached state, or runtime icon-manager processing. Attributing this to a missing font face is incorrect. Exactly which of these browser-side subpaths is active cannot be proved without capturing the real logged-in editor's `elementor.config.icons.libraries` **and** the `library.__hasheFa7CallbackGuard` installation state.
- The installed `editor.js` code's `onPickerShow` takes the shared array `elementor.config.icons.libraries`, and each asynchronous icon load does `icons[index] = lib`. Its `IconLibrary` keeps a **single mutable** `notifyCallback` and invokes it when async data normalize. An incorrectly routed callback can replace a tab's entry with another library. This is a **specific, directly verified code-level corruption path**, but the observed ~10 identical tabs also demand examination of the *initial* client-side library array; overwriting an array item does not enlarge the list. It is **not yet proved** that late callback installation alone caused all repeated entries.
- Our former JS "fix" routes callbacks by library name but doesn't establish a pristine authoritative library array at modal initialization, monitor accidental duplicates, or prove it installs before the first async icon load. Its Node mock did not use the real React modal/list lifecycle. Therefore its CI success was insufficient evidence for T16 or T22.
- Installed file changed to **Hashe 1.1.0-beta.3** after the previous beta.2 guard patch. The current beta.3 is from separate `hotfix/amari-native-compat-20261027` branch; GitHub SHA `04c7e62a9e9730be97dd7669caf384e2a71d834a`, not the unfinished integrated engineering branch `feature/fa7-ppp-integrated-20261027`. The installed beta.3 header at 04:37:48 UTC is different from the previously audited beta.2. This is **uncoordinated branch/deployment drift** under PPP.
- In beta.3 `hashe_fa7_native_compat_rules()`, globally scoped `.fas`, `.far`, `.fal`, and `.fad` rules with `!important` replace native icon font families, substitute Sharp Duotone for Classic Light/Duotone, and suppress Duotone pseudo-element layers. These are **source-confirmed changes** in both Elementor editor and public frontend; they're outside Kit discovery and violate W4/T21's no-global-override and true-native-behavior requirements. Even if some fallback glyphs appear, this is not "native icons preserved."
- Beta.3's verification document cites public page headless Chromium checks, **not** the logged-in Elementor popup; the supplied screenshot fails the missing acceptance criterion. No validated browser A/B with Hashe skipped exists. Our previous claim of a complete native icon repair was premature.
- Backup exists from preceding beta.2 hotfix: verified original plugin tarball `/bigdisk/home/devsasseagency/.hashe-fa7-backups/amari-native-icon-callback-20261027T042317Z.tar.gz` (private server location). Beta.3 also has a separately documented plugin+DB backup. **No restoration has been performed in this audit.**

## Corrected acceptance matrix for deployed Amari
- **T02 — FAIL:** displayed Hashe list is not exactly two distinct entries, despite PHP being correct.
- **T04 — PENDING/FAIL TO VERIFY:** native tabs exist but actual logged-in glyph comparison with Hashe disabled is not recorded. Don't change Kit to mask it.
- **T05 — FAIL:** repeated entire Brands category, even though earlier beta.2 label text once passed.
- **T16 — FAIL:** current editor runtime produced corrupted tab list; JS-only mock passes are not adequate.
- **T19 — PENDING:** no proper logged-in editor dual-browser before/after test.
- **T21 — FAIL:** beta.3 adds global `.fas/.far/.fal/.fad` `!important` font overrides.
- **T22 — FAIL:** repeated Hashe category names.
- **T03/T08/T09/T12/T13/T17/T23/T24 — PENDING** until integrated feature and visual acceptance. No general SVG/By Icon/Pro Plus claim.

## Required PPP corrective path — don't guess or hotfix again
1. **Freeze/reconcile:** record the exact installed beta.3 files/backup, GitHub hotfix and integrated branch SHA. Do not change Kit, mutate Elementor core, or add another live CSS workaround.
2. **Reproduce and instrument in an isolated editor/browser test:** inspect **actual** `elementor.config.icons.libraries` immediately before modal display and after async fetch (count, name, label, uniqueness), guard installation timing, network, localStorage quota, console. Establish whether the initial list is stale/multiplied or mutated during loading.
3. **Real controlled comparison:** with **same Kit** and verified backup, test with Hashe loaded vs skipped in logged-in Elementor on isolated QA workflow. Separate native glyph/resource issues from duplicated Hashe UI.
4. **Repair only on the approved integrated branch:** prevent callback cross-wiring without touching native CSS; restore/rebuild authoritative tab identity when stale client config occurs, but only after reproducing and testing. Remove the unapproved beta.3 global font overrides from any proposed integrated release.
5. **Regression acceptance:** exact **5+2 unique** tabs before and after opening, navigating, searching, saving and reopening; two Kit families correct; original tabs preserved, real native glyph baseline; repeat in browsers; no Kit changes; frontend saved icons render.
6. **Review, package, sign off, deploy:** CI + screenshot + network evidence, rollback test, owner approval. Only then call the new installer "ready." Do not update public `main`, stable v1.0.3, or executive ledger as completed.

## Audit scope
**Read-only against Amari and repository inspection in this audit.** Any GitHub documentation/QA tracking updates are solely on the isolated integrated development branch; no new Amari changes or Kit changes.
