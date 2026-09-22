# Contributing

Thanks for considering a contribution to `ariyx/http-client`.

## Prerequisites

- PHP `^8.3` with the `curl` extension
- Composer

## Setup

```bash
composer install
```

No environment variables, secrets, or external services are needed. Integration tests spin up a local PHP built-in web server automatically.

## Checks

Run the full suite before opening a pull request:

```bash
composer check
```

This runs, in order:

- `composer cs` — PHP-CS-Fixer dry run (PER-CS ruleset, risky rules allowed)
- `composer analyse` — PHPStan at level `max` over `src` and `tests`
- `composer test` — PHPUnit unit and integration suites
- `composer security` — `composer audit`

To auto-fix style violations, run `composer cs:fix`.

## Code style

- Add `declare(strict_types=1);` to every PHP file.
- Follow the existing architecture: `Client` delegates through `MiddlewarePipeline` to a `TransportInterface`. Keep new behavior in middleware or transports rather than in the client.
- Keep retry behavior conservative: only idempotent methods and transient failures (see `RetryMiddleware`).
- Preserve the secure defaults in `CurlOptions` (TLS verification on, redirects off).

## Pull requests

- Keep changes focused and covered by tests in `tests/Unit` or `tests/Integration`.
- Update `README.md` and `CHANGELOG.md` when behavior or the public API changes.
- Do not commit `vendor/`, Composer lock files, or local cache directories.
