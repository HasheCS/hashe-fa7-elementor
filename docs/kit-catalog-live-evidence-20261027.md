# Font Awesome Kit discovery — live engineering evidence (27 October 2026)

**Status:** Research + tested branch implementation. **Not** complete release sign-off.
**Source of requirements:** `HASHE_FA7_ELEMENTOR_COMPLETE_ENGINEERING_BLUEPRINT_2026-10-27.md`.
**Target:** WordPress Elementor plugin; one pre-existing Kit ID only; no additional customer credentials; preserve native libraries and licensing.

## Verified Web Fonts fixture (actual Amari public Kit)

- Kit `23f6a60c28`: `version=7.3.1`, `method=css`, `license=pro`. A JS embed is not an SVG Kit.
- Hosted Kit Web Fonts stylesheet: 184,544 bytes, 40 `@font-face` declarations.
- Distinct actual families and weights: `Font Awesome 7 Brands / 400` and `Font Awesome 7 Sharp Duotone / 400`.
- The Kit does not serve Classic Solid, Regular, Light or Classic Duotone Pro font faces; this is a capability limitation of that Kit, **not yet** evidence about Elementor's separate native font-loading baseline.
- CSS icon-name selectors matching `.fa-{id}{--fa:...}`: **5,407** unique selectors (includes icon aliases and generic mappings).
- Public Font Awesome release `7.3.1` metadata: **37** official family-style entries and **9** pages of `icons-minimal` at 500/page.
- Official public per-style metadata: `fab` **572** official icon IDs; `fasdr` **3,781** official icon IDs. All **572/572** and **3,781/3,781** respective IDs appeared among the hosted Kit CSS selector names.
- Important corrective nuance: the older static per-style Pro JSON assets in our repository happen to have **identical 3,781-name lists** for many styles. This is evidence of a non-maintained/general list construction, but **not by itself proof of a missing font glyph**: the live `fasdr` release metadata also lists 3,781 icons. An actual invalid-name case must be demonstrated before claiming one.
- **Implemented branch correction:** `hashe_fa7_release_style_catalog()` now obtains the official paginated family-style membership and caches a fully validated, release-specific catalog across styles; it no longer trusts the static Kit-mode Pro JSON files. Partial or structurally invalid API responses are discarded rather than cached.
- **Tests:** mocked multi-page style membership, native tab preservation, invalid input, failed-second-page retry, plus real Kit live API response all pass in current engineering CI (PHP 7.4/8.2/8.3 where applicable).

## Added Kit CSS intersection guard (engineering branch)

- `hashe_fa7_kit_snapshot()` extracts the actual served `.fa-{name}{--fa:...}` selectors, including comma-grouped aliases, from a licensed customer's **Web Fonts** Kit stylesheet; public REST style names are intersected with this inventory for Elementor AJAX.
- CI mock tests verify accepted and excluded icon names, alias grouping, invalid/missing Kit inventory, and API failure retry; live test verifies that the original two-style Kit keeps all its official selected-style icon IDs.
- This is a credible approach for a curated **Web Fonts By Icon** subset **if** its actual hosted stylesheet accurately reflects curation. T08 remains pending a real authorized By Icon Kit and a negative-control test.
- This does not imply support for SVG Kits: their resource and inventory structures must be independently verified.

## Verified SVG fixture (independent public site; no writes)

- `https://mandviwallamauser.com/` embeds Kit `7ba505d0c6`.
- Kit JS bootstrap, fetched with the owning site's public referrer, reports `version=7.3.1`, `method=js`, `license=pro`.
- Its bootstrap does **not** advertise a `subsetPath` or an authoritative `familyStylesPaginated`/per-icon manifest. The hosted `pro.min.js` URL serves an SVG/JS runtime, not the public Kit's definitive selected-icon inventory.
- Do **not** infer that `method=js` implies every icon/family is enabled or that its CSS kit parser works for SVG. The current beta correctly fails closed but SVG support is not complete.
- Public isolated Chromium visits to Amari and Mandviwalla confirm page load. The SVG runtime's icon registration in the authenticated Elementor picker still lacks a reliable browser test: the diagnostic Chromium run reported no loaded global Font Awesome icon registry, so it **cannot be marked passed**.
- **Performance plugin behavior found on live public HTML:** Amari's native Font Awesome Kit script is emitted as `<script type="litespeed/javascript" data-src="https://kit.fontawesome.com/23f6a60c28.js?...">` (LiteSpeed optimization); Mandviwalla emits `<script type="text/rocketlazyloadscript" data-rocket-src="https://kit.fontawesome.com/7ba505d0c6.js?...">` (WP Rocket delayed execution). This explains why initial isolated Chromium runs reported no executed Kit runtime, despite the correct bootstrap URL in both sites. Any browser acceptance test must account for delayed JS and inspect the logged-in Elementor editor separately. No cache/plugin settings were changed.
- **Isolated Chrome DevTools repeat after browser input:** dispatched pointer movement in a fresh, disposable headless Chromium profile; both optimizers then fetched their real Kit assets with HTTP 200. Mandviwalla loaded `7ba505d0c6.js`, `v7.3.1/js/pro.min.js`, and `pro-v4-shims.min.js`. Amari loaded `23f6a60c28.js`, Kit-specific `kit.css` and its v4 CSS compatibility files, all HTTP 200. Thus the missing Kit runtime on *initial untouched* public page load reflected delayed script execution, not absent Kit URLs or necessarily a Pro entitlement failure. This did **not** test logged-in Elementor picker or prove SVG icon rendering.
- **No changes** to either WordPress site or Kit settings during testing.

## Current external interface limit and required action

- Font Awesome GraphQL docs: `me.kit(token: ...).familyStylesPaginated`, `only`, and `iconVariantsPaginated` describe precise Kit subset membership; access to the Kit configuration requires **`kits_read`** authorization. [Official objects](https://docs.fontawesome.com/apis/graphql/objects) · [Official auth scopes](https://docs.fontawesome.com/apis/graphql/auth).
- The public release REST API supplies *version-level* metadata, **not** entitlement or selection data for one customer Kit.
- There is no verified equivalent `kit_id`-only public manifest for **By Icon** or subsetted **SVG** delivery. Do not claim complete support until this is proved with authorized Kits or clarified by Font Awesome.
- A follow-up technical query to Font Awesome would ask specifically for an authorized, credential-free manifest mechanism; no extra customer field, Hashe Pro font distribution, or third-party addon dependency is acceptable.
- No approved external email has been sent from this engineering session.
- A technical clarification draft addressed to Font Awesome support was saved in the Sophia Gmail account, CC Mamoon. It asks for a supported one-Kit-ID-only exact SVG/By Icon inventory mechanism. **The draft has not been sent.**

## Unresolved mandatory acceptance

- Real logged-in Amari Elementor with/without Hashe A/B under unchanged Kit, after verified backup.
- Web Fonts By Icon fixture with two permitted icons and negative membership assertions.
- Full SVG Kit catalog and frontend/Elementor picker demonstration.
- Pro Plus entitlement, release updates, last-good rollback, CSP/CORS, browser/iframe and saved-widget regression tests.
- T01–T24 full evidence and owner acceptance. **No complete WordPress installer or Amari deployment authorized by this document.**
