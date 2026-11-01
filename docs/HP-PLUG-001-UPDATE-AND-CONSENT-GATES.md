# HP-PLUG-001 integration contract — DRAFT, not production

This file documents the proposed boundaries for the Hashe Font Awesome 7 Elementor update pilot.

**Status: BLOCKED.** The server-side website phases and privacy/legal decisions are not implemented or approved. This document is not permission to deploy to \`plugins.hashe.com\`.

## WordPress-side updater (proposed)

- Embedded namespaced module: \`Hashe\\PluginTools\\V1\\UpdateClient\`.
- WordPress \`Update URI: https://plugins.hashe.com/hashe-fa7-elementor\` and \`update_plugins_plugins.hashe.com\`.
- **Provisional only**, pending HP-WEB-001 phase 6 approval: GET \`https://plugins.hashe.com/api/v1/updates/hashe-fa7-elementor\`.
- Read-only request, \`Accept: application/json\`, HTTPS certificate validation, no redirects, no site URL/domain, no installation ID, no consent/telemetry value, no cookie or credential.
- Cache only valid approved stable release metadata; fail closed on non-200, timeout, malformed JSON or invalid package.
- Proposed JSON: \`slug\`, \`status: "stable"\`, \`approved: true\`, \`prerelease: false\`, \`version\`, \`tag\`, \`sha256\`, \`package_url\`, \`details_url\`, \`tested\`, \`requires_php\`.
- The ZIP URL must match the exact immutable \`HasheCS/hashe-fa7-elementor\` release asset. The SHA must be produced from that exact asset and verified at the distribution and upgrade gate. No dynamic branch ZIP or third-party CDN substitute.
- A successful client unit test against a mock service is **not** a working live update. The website route currently returns 404.
- This is an engineering candidate module; endpoint path, response schema, versioned interoperability, ZIP checksum verification, upgrade retry/rollback, and release safety need sign-off.

## Optional installation reporting (not implemented)

The plugin must keep core icon functionality and updates independent of all analytics. HP-PLUG-001 additionally requires:

1. Admin-only invitation after activation; checkbox unchecked. Clearly separate **Allow & Continue** and **Skip without sharing**.
2. Before consent, disclose exactly what data is sent, to whom, when, how long retained, how to revoke, and where to find the privacy policy.
3. Default/skip: no optional network report; no remote installation ID.
4. Opt-in: store consent version/date; send only minimal explicitly disclosed data via HP-WEB-001 phase 7 service; never site content, user data, secrets or marketing consent.
5. Revocation: stop future reports and invoke approved server-side unlink/deletion process when available.
6. Verify offline, failed reports, reactivation, multiple Hashe plugins, and no-consent update flow.
7. **Do not collect personal data until phase 7 security, retention, deletion, abuse and legal approvals are complete.**

## Release gate

Do not merge, tag, publish, update Solutions, or present a Font Awesome download URL until the governing standards and clean install/upgrade/performance/security gates pass. The accepted v1.1.3-qa3 Solutions installation and GitHub stable v1.1.2 remain independent.
