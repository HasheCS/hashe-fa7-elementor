# Kit-driven Font Awesome catalogs (development notes; NOT RELEASED)

## Acceptance contract (Mamoon, 2026-10-26)

Hashe's independent Elementor plugin must read ONLY the existing
`elementor_font_awesome_pro_kit_id` setting for customer configuration.

- Empty setting: show Free Solid, Free Regular and Free Brands using the plugin's
  matching licensed Free assets.
- Configured Kit: show exactly the families/styles and individual icons that
  Font Awesome has made available through that Kit on that site. If the Kit
  selects only two family-styles, show only those two categories.
- Newly added Kit families/icons, including eligible Pro Plus icon families,
  must become discoverable without shipping a static, stale manifest.
- Do not distribute Pro/Pro Plus fonts/SVG/CSS, use Hashe credentials for
  customers, impose Hashe licensing, or depend on Mike's separate addon.
- Do not silently show unusable icon categories when Kit discovery fails.

## Current v1.0.3 limitations

The production PHP reads Elementor's Kit ID, but any nonempty ID results in
all 16 hardcoded Pro categories. JSON catalogs are bundled at version 7.3.1.
The editor callback guard avoids the localStorage quota regression: preserve it.

## Verified metadata sources

The Font Awesome public REST API offers:
- `/releases`
- `/releases/{version}/family-styles`
- `/releases/{version}/icon-packs`
- `/releases/{version}/icons-minimal` (paginated)

These describe Font Awesome releases and available icon styles; they do not
by themselves describe one customer's Kit selections.

The authenticated Font Awesome GraphQL API documents
`me.kit(token: ...).familyStylesPaginated` and Kit custom subsets, but
kit configuration metadata requires `kits_read` access.

## Initial isolated groundwork on this branch

`tools/sync_fontawesome_catalogs.py` generates metadata-only candidate JSON
from public REST endpoints, discovers newly added family-style prefixes without
hardcoding, and stores candidate output outside installed assets.

The candidate has not been wired into WordPress or installed Kit logic, and it
MUST NOT be released as proof of the desired Kit-specific behavior.

## Remaining verification gate

Prove with real, authorized test Kits (Free, Pro, Pro Plus, by-style two-family
Kit, by-icon Kit, full Kit, pinned/auto version, JS/SVG and Web Font modes)
whether the public *hosted Kit assets* that Elementor already loads reveal a
complete, reliable inventory of the Kit's families and icons WITHOUT an
additional credential. Validate domain-restricted Kits and failure behavior.

If Font Awesome's documented public hosted Kit assets cannot provide this
inventory, request the supported read-only discovery mechanism from Font Awesome.
Do not claim a Kit ID alone is sufficient before this is proven.

Only after this gate: implement automatic catalog refresh inside WordPress with
safe cache replacement, matching Kit version, Free font/CSS version safety,
editor/browser rendering checks, rollback, and upgrade documentation.

Existing main-branch v1.0.3 release is unchanged.
