# PPP evidence register — 2026-10-27

Status: **read-only baseline and isolated-branch engineering in progress**. This file is not a release approval.

## Immutable starting point

- Blueprint: `HASHE_FA7_ELEMENTOR_COMPLETE_ENGINEERING_BLUEPRINT_2026-10-27.md` (user-provided Project source), acceptance T01–T24.
- Public `main` commit: `3c5d19794529595e5a0991610a117a46381079bb`.
- Public recommended release: `v1.0.3`.
- Prior beta branch: `feature/fa-kit-catalog-sync-20261026`, base commit `cb8cad42da7bc5457a5524e9554ff1f004d092fa` (`v1.1.0-beta.2`).
- Active integrated-development branch: `feature/fa7-ppp-integrated-20261027`. Do NOT replace v1.0.3 or Amari's existing beta in this stage.
- Amari development website: `https://dev.sasseagency.com/amari/`.
- Amari Elementor MCP route: Desktop Commander → `ovh-new.hashe.com` → `amari-homes-elementor`; authenticated read-only tool listing passed (10 annotated read-only Elementor operations). WordPress Umbrella directory confirms Amari is registered **only** with an Elementor MCP target, not with a WordPress plugin-inventory target.
- Read-only public HTTP HEAD to the Amari root returned 200 and WordPress REST link; this does NOT prove editor, plugin versions or font previews.
- Follow-up public HTML inventory (read-only) returned HTTP 200, 122,479 bytes, and referenced `https://kit.fontawesome.com/23f6a60c28.js?ver=4.3.2`. This proves the test Kit embed is present on the public Amari page; the `ver` parameter by itself must not be treated as a confirmed Elementor Pro installed version. Browser assets may differ inside logged-in Elementor editing.

## Grounded previous user observations

- Test Kit `23f6a60c28`: FA 7.3.1 Pro, selected Web Fonts families Brands and Sharp Duotone Regular, JS embed.
- beta.1: 2 Hashe tabs visible; label symbols blank; user screenshot shows contents of Brands populated.
- beta.2: label symbols corrected and duplicate Brands label corrected; native Elementor Light/Duotone Pro tiles remain empty squares. No evidence yet whether the same occurs when Hashe is disabled.
- Previously the v1.0.3 localStorage regression was corrected and Amari editor function was confirmed by owner.

## Read-only source/architecture findings

1. Our plugin registers only `elementor/icons_manager/additional_tabs`. Elementor core returns `array_replace( native, additional )`; the registration function does not remove or replace native `fa-regular`, `fa-solid`, `fa-brands`, native Pro Light/Duotone.
2. beta.2 uses user Kit `window.FontAwesomeKitConfig` and By Style subset stylesheet `@font-face` declarations to discover permitted styles.
3. beta.2 currently **cannot** resolve SVG Kits or exact By Icon curation. For SVG, the bridge fails closed and preserves native tabs. For By Icon, public release metadata is not a substitute for an authenticated Kit subset inventory.
4. Font Awesome documentation confirms By Style subsets serve every icon in chosen styles, By Icon serves hand-picked icons, and SVG is not offered for By Style subsets. Its authoritative GraphQL `Kit.familyStylesPaginated` / `only` fields require Kit-scoped authenticated read permissions. No extra customer credential can be silently added contrary to product requirement.
5. A restricted By Style Kit cannot provide unselected Pro fonts. This is an **evidence-backed capability constraint**, but Amari's native-Pro cause remains unclassified until a controlled Hashe-enabled/disabled A/B test using the *same Kit ID* is performed.
6. **Live Kit CDN CSS inspection (read-only, 27 Oct 2026):** Kit `23f6a60c28` reports `method=css`, `version=7.3.1`, `license=pro`, with 40 `@font-face` rules. After deduplication the only hosted font families/weights are `Font Awesome 7 Brands / 400` and `Font Awesome 7 Sharp Duotone / 400`. There are no Classic Pro Regular(400), Solid(900), Light(300), or Classic Duotone(900) font-face rules in this Kit stylesheet. This proves what this Kit serves; it does not prove whether Elementor separately includes any fallback font, and does not replace the controlled Amari A/B.

## Changes so far in this branch

- Enlarged PHP regression suite to protect all five native Pro tabs, exact two-style output, additions/removals of Kit styles, fail-closed behavior for unverified SVG and malformed Kit configuration. These are automated simulation checks **not** substitutes for in-editor tests.
- No changes to the Amari website, Kit configuration, current recommended GitHub release, or existing beta branch.

## Outstanding verification blockers

- **T04/W1:** Real authenticated Elementor editor A/B (with and without Hashe, same Kit) requires scoped backup and approval for controlled plugin toggle; no toggle has occurred.
- **T09/W2:** User's original all-styles SVG Kit ID was not furnished in this thread; must use a properly authorized example / saved configuration when accessible, and verify editor/frontend. No assertion of SVG support until actual proof.
- **T08/W2:** No authorized By Icon Kit example used yet. Unknown whether public hosted assets reveal exact curated icon inventory in all delivery modes. Official GraphQL Kit metadata requires `kits_read`. Do not fake support.
- **T12:** Pro Plus entitlement kit test pending.
- **T13/T23:** End-to-end automatic metadata update and rollback not implemented.
- **T19/T21:** Real browser compatibility, existing saved widget frontend, other library coexistence pending.

## Mandatory next actions

1. Obtain an authorized, read-only logged-in browser baseline or narrowly approved controlled A/B after verified backup. Do not use production shell to edit plugin.
2. Prove SVG/By Icon Kit metadata discoverability under the one-ID-only contract, or obtain Font Awesome's supported solution before implementing speculative parsers.
3. Build true dynamic cache versioning with atomic verification/last-good and source-vs-Kit intersection, then verify on real Elementor + frontend.
4. Keep all T01–T24 marked as not passed until evidence exists; do not package/promote as complete.
