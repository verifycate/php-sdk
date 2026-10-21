# Changelog

All notable changes to this project are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project uses
[Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.0] - 2026-10-21

### Added

- `Client` with injectable transports and configuration for the API key,
  workspace slug, base URL and timeout.
- Certificate resources: list, get, create, update and delete.
- Issue resources: list for a certificate, import recipients, get, update,
  delete, issue, reissue and resend an unlock form.
- Recipient resources: list across the workspace and get one recipient with
  every issue they hold.
- Public verification resources: fetch a public certificate and verify its
  signature without an API key.
- `CurlTransport` (default, `ext-curl`) and `Psr18Transport` for any PSR-18
  client and PSR-17 factories.
- Typed DTOs with status helpers and access to the untouched API payload.
- RFC 7807 `ApiException` with retry classification and `Retry-After`.
- Committed public OpenAPI snapshot and a drift test for every operation,
  policy, parameter, request schema and modeled DTO field.
- Recorded conformance fixtures for 400, 401, 402, 403, 404, 422, 429 and 500.
