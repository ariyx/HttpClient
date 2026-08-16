# Changelog

## v3.0.0 - 2026-08-16

- Rewritten as a standards-based PSR-18 HTTP client backed by cURL.
- Added PSR-7 and PSR-17 interoperability.
- Added an injectable transport architecture and client middleware pipeline.
- Added conservative retry support for transient failures.
- Added secure TLS defaults and stricter request/response validation.
- Added PHP 8.3, 8.4 and 8.5 CI coverage.
- Replaced the legacy v2 request, response, authentication, cache and configuration APIs.