# Amari integrated QA deployment — 27 October 2026

## Change deployed by engineering

**Environment:** Amari development, `https://dev.sasseagency.com/amari/`.

**Installed WordPress plugin:** `hashe-fa7-elementor`, version `1.1.0-qa.20261027`, **active**.

**Source of installed plugin:** consolidated engineering branch `feature/fa7-ppp-integrated-20261027`, plugin-code commit `c5bf051c9dddc0f89bb698445717181884c95197`. WordPress plugin files were exported from that Git revision and installed through the existing authorized shell account. No manual owner upload or new API credentials were required.

**Previous installed version:** `1.1.0-beta.4`. Before replacement, full plugin archive and WordPress database SQL dump were taken and integrity-checked in a restricted-access backup directory outside `public_html`. Restoration source preserved. No Kit ID option changes.

**Existing Elementor Font Awesome Kit:** `23f6a60c28`, unchanged. The plugin automatically reuses this existing setting.

## Actual live WordPress and HTTP checks after deployment

The **installed** plugin (not just a separate test checkout) was loaded by WordPress CLI, and the real Elementor Icons Manager returned exactly these seven unique tabs in order:

1. `fa-regular`
2. `fa-solid`
3. `fa-brands`
4. `fa-light`
5. `fa-duotone`
6. `hashe-fa7-fab`
7. `hashe-fa7-fasdr`

The first five native Elementor Pro entries retained their names and icon metadata, with secure root-relative catalog JSON paths. All five actual Pro catalog paths and the Hashe editor callback guard JavaScript were checked via public HTTPS HTTP requests and returned **200**.

Existing live Kit detection: `version=7.3.1`, `method=css`, `subset_mode=full-family-fonts`, selected **Brands and Sharp Duotone Regular** only.

Real installed WordPress AJAX catalog testing using an ephemeral WP-CLI editor context (no new login cookies/sessions or account changes):

- Brands `fab`: **572** icon names, valid Elementor `{icons:[...]}` JSON.
- Sharp Duotone Regular `fasdr`: **3,781** icon names, valid JSON.
- Unselected Solid `fas`: **zero**, valid `{icons:[]}` with an explicit nonfatal diagnostic.

Real installed Elementor `Icons_Manager::render_icon` test:

- `hashe-fa7-fab`: `<i class="fa-brands fa-github"></i>`
- `hashe-fa7-fasdr`: `<i class="fa-sharp-duotone fa-regular fa-house"></i>`

All site-side assertions passed. `wp cache flush`, `wp litespeed-purge all` and `wp db check` all passed. GitHub PHP and full PPP automated engineering CI passed on the installed source commit. Browser-based isolated hosted Kit/Web Fonts, full SVG Kit, Free fonts and modal race fixtures passed before installation.

## Remaining qualification

The installation and programmatic Elementor integration are **verified**, but I have **not** executed the final authenticated, human-visible Elementor popup click/search/save/reopen sequence inside the owner's login. Public full-release promotion remains blocked by the separate T01–T24 release gate and private Kit inventory limitations. Do not describe this QA installation as a publicly released universal By Icon/SVG subset implementation. In particular, the existing selected Kit does not supply every Classic Light/Duotone Pro font; the plugin will not fabricate paid styles or ask the user for read access tokens.

**Distribution remains unchanged:** no new public release, no promotion to `main`, and no requirement for customers to obtain a second API token. The Amari development environment now runs the consolidated candidate directly.
