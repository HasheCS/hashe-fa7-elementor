# HASHE FA7 Elementor — Optional Reporting and Updater Client Contract

**Scope:** GitHub source for Hashe Font Awesome 7 Icon Library for Elementor.
**Status:** Plugin-side candidate only. No changes to plugins.hashe.com or Solutions.
**Date:** 2026-11-02.

## Independent WordPress updates

The embedded versioned Hashe V1 UpdateClient registers the standard Update URI filter. It requests only approved stable release metadata through HTTPS, without attaching site URL, installation ID, consent choice or analytics data.

For a newer approved version it accepts only the exact tagged release ZIP URL of HasheCS/hashe-fa7-elementor. During the WordPress upgrader_pre_download hook it downloads that package and verifies SHA-256 against the approved release manifest **before** returning the temporary ZIP for installation. A missing/mismatched manifest, bad checksum, transport error, or unintended package fails closed. A verified ZIP remains subject to WordPress core's own filesystem/extraction/install and rollback handling.

**Integration pending:** The website-owned update metadata endpoint and its approved stable release manifest have not been verified live. Mocked feeds and checksum tests do not count as a complete live WordPress update.

## Optional installation reporting

Plugin-side code: includes/hashe-core/v1/consent-client.php, isolated from UpdateClient.

- The default is OFF. Installing, activating, updating, or skipping never sends analytics, generates a site identity, or schedules analytics calls.
- A WordPress administrator can review an activation notice and the Settings > Hashe FA7 Privacy page. Skip/Continue without sharing records only a local declined preference. The page never blocks icon rendering or updates.
- Allow & Continue appears only when a complete approved reporting contract is registered. Checkbox is initially unchecked, requires an explicit admin submission with nonce and capability checks. It records policy version, consent time and an installation UUID generated **only after opt-in**.
- Minimal consenting payload: site URL, plugin slug/version, random installation ID, report time and last-reported-active status. No user email, site content, Kit credential, password or diagnostic dump.
- The reporting endpoint and revocation endpoint are pinned to specific HTTPS URLs; no redirects are followed, outbound authentication must be provided by an approved server integration, with no credentials stored in the GitHub source. Successful reports schedule at most one future report after 30 days; failures retry after a day; the callback checks consent and policy version before each request.
- Revocation cancels future reporting BEFORE requesting deletion. Failed/unavailable deletion remains visibly revocation-pending for an administrator-initiated retry; no further reports occur. Deactivation cancels analytics cron, and a previously revoked status is never automatically reinstated. Uninstall clears local consent data; remote deletion is not guaranteed on uninstall without prior explicit revocation.
- Configuration uses the hashe_fa7_optional_reporting_contract filter; empty by default. Required contract keys: version, privacy_url, retention, cadence, report_endpoint, revoke_endpoint, authenticated_headers. The only accepted cadence is exactly Every 30 days, consistent with the actual scheduler. Pinned service paths: /api/v1/telemetry/report and /api/v1/telemetry/revoke. Filter wiring MUST NOT be activated until the external service owner supplies an independently validated authentication mechanism, retention/deletion policy, privacy URL, service availability, and approved request contract. A site's arbitrary form input cannot enable reporting.
- **No external service or live analytics is turned on by this candidate.** Until the contract is approved, the Allow button is absent and Skip works. Plugin icon operations and native updates remain independent.

## Remaining integration work owned outside this plugin repository

- External update endpoint must expose approved immutable GitHub stable release metadata, including a verified SHA-256 of the precise public ZIP; perform a real update to a disposable WordPress installation.
- External telemetry service must specify/verify authentication, abuse prevention, retention/deletion, privacy disclosure URL, consent schema, and actual server-side removal. Only after those decisions can the plugin's default-off consent integration be activated and tested in end-to-end conditions.
- Do not interpret website milestone completion as plugin compliance. Do not make website layout/CSS/DB/code changes in this plugin workstream.

## PPP exit

Current plugin-side source and mocked service tests may be committed to draft PR #7 and checked with PHP 7.4/8.2/8.3, isolated WordPress clean install/upgrade and static checks.

Publishing a stable v1.1.3 release and claiming live update/reporting support remain gated on complete independent verification and final explicit approval.
