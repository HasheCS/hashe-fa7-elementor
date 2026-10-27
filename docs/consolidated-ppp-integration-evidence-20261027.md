# Consolidated Hashe Font Awesome 7 Elementor engineering evidence

Date: 2026-10-27
Branch: `feature/fa7-ppp-integrated-20261027`
Latest audited change before this document: `40bfd561428ed79a1555ed715a0e90199bcc4c47`
Public `main` / recommended stable release: **unchanged**, `v1.0.3`.
On Amari: previously installed `1.1.0-beta.4` hotfix, **not** the consolidated branch. No consolidated package installed.

## Completed integrated engineering and direct verification

1. **Five native tabs + two selected Kit tabs:** exact identities and titles preserved. Elementor 4.3.5 editor-mode WP-CLI canary on real Amari, with installed Hashe skipped and integrated source loaded from an isolated checkout: `fa-regular,fa-solid,fa-brands,fa-light,fa-duotone,hashe-fa7-fab,hashe-fa7-fasdr`. `EDITOR_BASELINE=7 unique`. Same Kit `23f6a60c28`.
2. **Browser callback regression:** Name-keyed in-memory handling for native and Hashe async catalogs, protection against stale repeated Brands labels. Automated out-of-order 16-category test, third-party/virtual tabs, quota, cache clearing. Actual Chromium DOM icon tab fixture passes; this is **not** a logged-in Elementor screen capture.
3. **No-Kit Free fonts:** Actual independent Chrome browser loaded and rendered packaged Free Solid house, Free Regular star and Free Brands GitHub; `document.fonts.check` succeeded for all three faces. Not a substitute for testing an Elementor widget save/reopen.
4. **Real Kit Web Fonts + JS embed:** Kit `23f6a60c28` reveals exactly `fab` and `fasdr`. Public Kit CSS `@font-face` styles verified. Release-specific official public `/family-styles` and all `/icons-minimal` pages validated, only Kit-present IDs included.
5. **Real full SVG+JS Kit:** The publicly embedded original Kit `7ba505d0c6` identifies `method=js`, `startupFilename=pro.min.js` and no `subsetPath`; official public standard family-style list matches. A public HTTP request without a Referer returned 403; the legitimate same-site Referer returned 200. Fetch path corrected to use current WordPress site's Referer, not another customer's domain.
6. **By Icon CSS:** Exact single-family CSS-selected ID intersection demonstrated; incomplete *multi-family* Web Fonts subset fails closed with HTTP 422 to avoid inventing style-specific icons. A flat CSS Unicode map cannot prove which glyph is included in each separate font subset.
7. **Pro Plus:** Mock hosted Kit font faces for Notdog Solid (`fans`) and Whiteboard Semibold (`fawsb`) create exactly those Kit-enabled tabs; ordinary Pro full SVG does not falsely claim Pro Plus. Actual authorized Pro Plus Kit acceptance is still unavailable.
8. **Versioned catalog integrity:** Public REST release-specific membership, Free-vs-Pro license filtering, validated pagination, single-flight index build, last-known-good cache retention on failures, hourly WordPress cron prewarming plus on-demand editor fallback.
9. **Security:** Strict HTTPS Kit/CDN/API URL allowlist and redirects disabled, bounded remote response sizes; current-site Referer to Kit endpoints only; logged-in editor capability plus nonce on dynamic AJAX; no Pro glyph/font code shipped; no extra credential field; no global native `.fas/.far/.fal/.fad` overrides.
10. **Package:** Deterministic internal QA ZIP script produces one WordPress plugin root containing 34 files, plugin header/readme both beta.4, bundle integrity verified using `unzip -t` and SHA-256. Tested `fea3751f566eef7710956689b84eb13d9a91f241d74da18f27645a0f64c54cbe` at earlier intermediate SHA `31aa0b9ffbc061b34479f8b9e506569081d6866c`; ZIP is **unreleased** and must be rebuilt after subsequent changes. GitHub CI includes repeatability and installable root tests.

## Missing release gates — do not misrepresent as completed

- **T04/T19/T22:** Authenticated live Elementor picker, real icon tile/search/insert/save/reopen, Firefox or other secondary browser, iframe, actual frontend A/B with Hashe skipped. Real WordPress PHP registration and independent Chromium fixtures are not a substitute.
- **T08:** Exact *multi-style* Web Fonts By Icon where font-specific selections differ: no verified public per-family membership in hosted CSS. One-style By Icon works. Fail-closed semantics are explicit.
- **T09:** Full SVG JavaScript Kit bootstrap discovered, but its real Elementor icon preview and frontend SVG icon rendering under delayed WP Rocket/LiteSpeed have not passed.
- **T12:** Actual Pro Plus customer's Kit and authenticated rendering unavailable; mock Notdog/Whiteboard test only.
- **SVG By Icon:** A curated SVG Kit may expose `subsetPath`, but no authenticated, supported public icon-by-family manifest was established from Kit ID alone. Fail closed rather than fabricate icons.
- **T06/T07/T13/T17/T23/T24:** Live Kit mutation across releases, selected-style changes, all metadata failure recovery, and tested install/upgrade/rollback have not all been executed on the final integrated candidate. Automatic refresh/packaging code exists but does not make these tests pass by assumption.

### Proven official Font Awesome dependency

Font Awesome's own GraphQL schema exposes exact `Kit.familyStylesPaginated`, `FamilyStyleSubset.only` and `Kit.iconVariantsPaginated` fields, with variant-level per-family membership. These require the **`kits_read` authorization scope**. Public release REST metadata only identifies icons and styles available in the release, not any private customer's exact selected combinations.

Official sources:
- https://docs.fontawesome.com/apis/graphql/objects
- https://docs.fontawesome.com/apis/graphql/auth
- https://docs.fontawesome.com/web/setup/use-kit
- https://docs.fontawesome.com/web/dig-deeper/kit-package-api

This project forbids a second customer token; don't quietly introduce one, claim the public release endpoint is a substitute for private per-Kit configuration, or redistribute paid Kit packages. If Font Awesome offers an official Kit-ID-only inventory in the future, validate that precise endpoint before enabling those modes.

## Release decision

**BLOCKED.** No claim of complete T01–T24 acceptance, no public `main` promotion, no final installer handed to owner, no CEO work-ledger done entry. Isolated integration work is meaningful and reproducible, but no authenticated full-feature test or documented Font Awesome-supported Kit-ID-only multi-style/SVG-By-Icon resolution exists.

## Supplemental real-browser and packaging evidence

- **Actual restricted Web Fonts Kit frontend:** Isolated Chrome navigated to public Amari, manually executed the site's already authorized JS-embedded Kit `23f6a60c28` within that page (no server changes), loaded its real Kit CSS (HTTP 200), and rendered real `fa-brands fa-github` and `fa-sharp-duotone fa-regular fa-house` glyphs. Computed CSS correctly used `Font Awesome 7 Brands` and `Font Awesome 7 Sharp Duotone` at weight 400, both web fonts loaded, and original Font Awesome CDN WOFF2 font assets returned HTTP 200. Technology `method=css`, despite embedding via JavaScript. Executable fixture: `tests/live-css-browser.cjs`.
- **Actual full SVG Kit frontend:** Isolated Chrome navigated to the public Mandviwalla site, triggered its own authorized `7ba505d0c6` Kit JS in page context to avoid WP Rocket's intentional delayed loading, and observed `fa-solid fa-house` and `fa-brands fa-github` converted into actual `svg.svg-inline--fa` elements (prefixes `fas` and `fab`). Official Kit JS, `pro.min.js`, and corresponding per-icon SVG assets all HTTP 200. The customer and site were not modified; no paid SVG data was bundled. Executable fixture: `tests/live-svg-browser.cjs`.
- Both real browser fixtures are now included in the isolated CI workflow. Their success verifies **hosted icon rendering on the public page**, **not** an authenticated Elementor picker, saved widget values, or Kit access from an arbitrary unrelated domain.
- Development plugin identity was separated from Amari's already installed beta.4: consolidated branch is `1.1.0-qa.20261027`, **never deployed as a replacement for beta.4**. Packaging tool `tools/build_zip.py` verifies the plugin header matches WordPress `readme.txt`, deterministic ZIP root, Free-only bundled WOFF2 fonts, and absence of native Pro CSS overrides.
- Removed obsolete `assets/css/native-pro-compat.css` entirely from consolidated plugin source, rather than merely leaving it unreferenced. Public stable `main`/v1.0.3 untouched.
- Actual Font Awesome GraphQL docs specifically establish that Kit's exact `familyStylesPaginated`, `iconVariantsPaginated`, and per-family `only` fields expose the authoritative subset inventory behind `kits_read`; no equivalently reliable public Kit-ID-only data source has been established for exact multi-style custom subsets. See https://docs.fontawesome.com/apis/graphql/objects and https://docs.fontawesome.com/apis/graphql/auth.

**This remains engineering QA, not completed T01–T24 signoff.**

## Final internal QA artifact and CI evidence

- Reproducible ZIP assembled from integrated source SHA `ff6e8c525d0f7cb6985b02ae47acd13968c06d16`.
- Header / `readme.txt` identity: **`1.1.0-qa.20261027`**; ZIP has **33** files and one WordPress plugin root.
- SHA-256: **`c768682d5a795d0e6603ff162e653207ec762918a3677c175f69d0317d9aaa5a`**.
- `unzip -t`: clean; no `native-pro-compat.css` or bundled Pro WOFF2; internal server QA artifact held outside web root, **not published to the owner or public**.
- GitHub Actions at the same tested SHA reported **success** for PHP smoke tests, full PPP regression suite, public Kit metadata, both actual Kit browser rendering modes, Free fonts, and native-category callback DOM tests.
- Not a released ZIP: Font Awesome `kits_read` dependence for exact complex subsets and authenticated Elementor acceptance remain outstanding.
