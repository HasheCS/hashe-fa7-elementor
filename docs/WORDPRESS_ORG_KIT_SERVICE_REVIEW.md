# WordPress.org review evidence: customer-configured Font Awesome Kit service

Status: **human reviewer decision required**. This document does not waive the failing Plugin Check rule or authorize directory submission.

## External service and purpose

Hashe Font Awesome 7 Icon Library for Elementor integrates with the third-party **Font Awesome Kit service** selected and licensed by the site's own administrator through Elementor Pro. The service provides meaningful Font Awesome Free, Pro and Pro Plus icons and customer-uploaded Kit icons. Without a configured Kit, the three bundled Free icon families remain usable without the Kit lookup.

- Provider: https://fontawesome.com/
- Kit host: `https://kit.fontawesome.com`
- Customer Kit-upload host: `https://kit-uploads.fontawesome.com`
- Font Awesome public release metadata: `https://api.fontawesome.com`
- Provider terms: https://fontawesome.com/tos
- Provider privacy policy: https://fontawesome.com/privacy

When the administrator has configured a valid 10-character Kit ID, the plugin retrieves `https://kit.fontawesome.com/{kit-id}.js` **server-side as text**, parses the `window.FontAwesomeKitConfig` JSON-like configuration payload, and validates the current customer Kit identifier before using style/library metadata. The plugin does **not** evaluate or execute the downloaded JavaScript as PHP or WordPress plugin code; it does not install PHP/JS updates from the service. The Kit and its separately licensed assets remain under the customer's Font Awesome agreement. Other Kit-hosted media/CSS/SVG dependencies are disclosed in the distributed `readme.txt`.

Kit requests can convey the administrator-configured Kit ID, requesting server IP and (for the Kit lookup) the WordPress home URL in the Referer header. There is no optional Hashe installation analytics transmission before affirmative administrator consent. The native WordPress.org build has no Hashe/GitHub external executable updater, and the standard Free icon libraries work without a configured Kit.

## Why the automated check currently fails

GitHub Actions `wporg-install` runs official Plugin Check 2.1.0. Its `PluginCheck.CodeAnalysis.Offloading.OffloadedContent` scan reports `ERROR` against the `kit.fontawesome.com/{id}.js` URL in `hashe-fa7-elementor.php`, where the plugin requests the customer-owned external Kit's configuration. The failure is retained in CI, not hidden or suppressed.

This integration is offered as a documented third-party icon **service**, not as arbitrary third-party code offloading. WordPress.org guidelines permit SaaS plugins and certain documented third-party service dependencies on a case-by-case basis; this does **not** guarantee approval. Authoritative references:
- https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/ (guidelines 6, 7 and 8)
- https://developer.wordpress.org/plugins/wordpress-org/common-issues/ (remote-file service exceptions and third-party service disclosure)
- https://docs.fontawesome.com/web/use-with/wordpress (Font Awesome's own WordPress Kit integration)

## Reviewer question and release gate

Please determine whether this administrator-configured licensed Font Awesome Kit integration qualifies for the documented-service exception, or specify a compliant design preserving legitimate customer Kit and custom-icon functionality.

**Do not** remove Pro/Pro Plus features, conceal external network contact, bypass the scanner, or claim this issue passed without an accepted technical/policy outcome. WordPress.org admission remains pending an independent reviewer decision. GitHub independent-channel publication is separately gated by full functional/security/compatibility acceptance and owner approval.
