# Integrated FA7 Elementor engineering state — 27 October 2026

**Status: DEVELOPMENT ONLY — NO FINAL RELEASE.** Source of truth is this consolidated branch. Do not merge, package or require owner to install again until mandatory acceptance scenarios are evidenced. The approved baseline is `HASHE_FA7_ELEMENTOR_COMPLETE_ENGINEERING_BLUEPRINT_2026-10-27.md`.

## Code integrated

- Incorporated the Amari beta.4 callback and canonical Elementor tab identity repair into the one integrated engineering branch. Removes beta.3's global native `.fas/.far/.fal/.fad` CSS overrides. Preserves Elementor/other plugin tab identities, virtual All and Recommended tabs, and good cached native icon data on normal reopening; corrects stale duplicate Brands tab state before modal opening.
- Kit `23f6a60c28` Web Fonts with JS embedding: only Brands and Sharp Duotone Regular authorized, derived from the existing Elementor Kit ID and served stylesheet. No Kit change or additional customer setting.
- Web Fonts Kit subsets: intersect official public version/style icon IDs with actual hosted Kit CSS icon selector names; reject names without a corresponding served selector. Synthetic two-icon curated Kit test passes; **real By Icon Kit fixture remains a release gate**.
- Full Web Fonts Kit without `subsetPath`: use sanitized `startupFilename` and public licensed full CSS path; map `Font Awesome 7 Pro` or `Font Awesome 7 Free` to Classic family metadata. Mocked Pro/Free full cases pass.
- Full SVG+JS Kit with no subset: recognize the public full release runtime filename and license, infer permitted ordinary public release family styles without bundling any paid SVGs, preserve standard Font Awesome Kit script rendering. Deliberately fail closed for a curated SVG subset whose exact icon inventory cannot be authenticated with Kit ID alone. The actual authenticated editor-and-frontend SVG rendering gate remains OPEN.
- Free Kit catalog filter: free-license public metadata only; Pro-only names under the same style prefix are excluded even in full Free SVG mode.
- Dynamic metadata: version-scoped full release family/icon catalog from public REST; no hardcoded 16-style family list, validated all-page atomic activation, cached last complete version during refresh errors with retry throttling, and one DB-backed single-flight build lock across simultaneously loaded Elementor tabs; preserves pinned Kit version boundaries.
- Security: strict HTTPS allowed hosts/path/query/Kit token matching, no HTTP redirects, bounded response size and timeouts, no extra account credentials or paid asset distribution.
- Integrity: final-release acceptance gate explicitly refuses incomplete/failed T01–T24 and requires verifiable evidence and owner approval for a genuine external limitation.

## Evidence

- Consolidated branch GitHub CI: `FA7 PPP engineering regression checks (not a release)`, `PHP smoke tests`, PHP 7.4/8.2/8.3; isolated Chromium picker DOM test; live public two-style Web Fonts Kit and official public release metadata.
- The earlier Amari screenshot of beta.3 duplicated Brands labels and proved real user-acceptance failure; later isolated DOM/Node tests and installed beta.4 server checks **do not substitute for a new logged-in Elementor screenshot**.
- Separate deployed beta.4 remains a **targeted development-site correction**, not the integrated source candidate. The integrated branch changes in this document have **not** all been installed in Amari, and public main and v1.0.3 are untouched.

## Blocking verification (cannot claim complete)

1. Live authenticated Elementor modal and frontend: current seven unique entries, original native icons, saved widget reload, native and Hashe glyphs, Firefox/Safari comparison.
2. By Icon Web Fonts real two-icon authorized Kit fixture; no false positives for unselected release icons.
3. Real full SVG Kit in editor and frontend, including saved references; curated By Icon SVG inventory supported by an official Kit API or explicit accepted limitation. The documented Kit configuration GraphQL fields require `kits_read` and no verified public Kit-ID-only exact subset manifest exists.
4. Pro Plus entitlement and full-render test using a real appropriately licensed Kit; cannot claim Pro+ simply because public release metadata lists family names. Font Awesome documents a Kit Package token requirement for Pro+ icon bundles.
5. Site upgrade/rollback with packaged build, accessibility, CSP/CORS, cache expiration, security reviews, all 24 acceptance cases, and owner installation signoff.

## Deployment control

Do not touch Mamoon's Kit. Do not change production or public GitHub main. No new WordPress-installable ZIP for acceptance until test matrix reaches approved release gate. All remaining work belongs to this integrated branch, not to disconnected Amari hotfixes.
