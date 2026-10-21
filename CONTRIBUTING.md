# Contributing

Thanks for helping improve the Verifycate PHP SDK.

## Development setup

```bash
composer install
composer check
```

`composer check` runs PSR-12, PHPStan level 8 with PHP 7.4 semantics, and the
PHPUnit suite (unit, conformance and OpenAPI drift tests).

## Compatibility

The SDK must keep working on PHP 7.4, because the WordPress plugin supports
PHP 7.4. Avoid language features introduced after 7.4: union types,
constructor property promotion, enums, readonly properties, `match`, `?->`,
`str_contains()` and named arguments in library code.

`composer.json` pins `config.platform.php` to 7.4.33 so dependency resolution
stays compatible even when you run a newer PHP locally. CI covers 7.4 through
8.4.

## API changes

`openapi/openapi.json` is a snapshot of the public document. When the API
changes:

```bash
composer sync-spec https://api.verifycate.com/api/v1/openapi.json
composer check
```

`tests/Spec/SpecDriftTest.php` is the contract. If a path, policy, parameter,
request schema or modeled field moves, the test fails before release. Update
`src/Operations.php` and the DTOs with the snapshot, and add a conformance
fixture for any new error shape.

## Pull requests

- Keep changes focused and add tests for new behaviour.
- Run `composer check` before opening a pull request.
- Sign off every commit (`git commit -s`) to certify the Developer Certificate
  of Origin.
- Describe the user-visible change and any API drift in the pull request.

## Security

Do not open a public issue for a vulnerability. See [SECURITY.md](SECURITY.md).
