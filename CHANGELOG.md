# Changelog

## v0.1.1 - 2026-06-18

### What's fixed

- Prevent permanent Mux API errors from breaking Statamic asset uploads when queues run synchronously.
- Store permanent Mux API failures on the asset metadata with `status: errored` and the Mux error details.
- Keep transient Mux/server errors retryable by rethrowing non-4xx API failures.
- Collapse the Mux metadata field behind a revealer button in asset blueprints so it does not dominate the asset edit screen.

## v0.1.0 - 2026-06-18

### What's new

- Initial public release of Statamic Mux Id for Statamic 6.
- Creates Mux video assets from Statamic asset uploads and keeps Mux metadata on the asset.
- Adds a read-only `mux_data` field to asset blueprints so Mux metadata is visible in the control panel.
- Exposes the Mux playback ID through GraphQL as `mux_playback_id`.

### What's fixed

- Webhooks can now be secured with `MUX_WEBHOOK_SECRET` using Mux signature verification.
- Webhook responses now return clearer status codes for ignored, malformed, unauthorized, and unmatched events.
- Mux asset creation is idempotent to avoid duplicate Mux assets from repeated upload/save events.
- GraphQL playback ID resolution now handles missing playback IDs safely.

### Maintenance

- Requires PHP 8.3+ and Statamic 6.
- Upgrades `muxinc/mux-php` to `^3.21`.
- Adds Pest tests, Pint formatting, CI, Dependabot, and release/changelog automation.
