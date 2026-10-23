# Verifycate PHP SDK

Official PHP client for the [Verifycate](https://verifycate.com) digital
credential API. It covers the issuance surface used by integrations: listing
certificates, importing recipients, issuing and reissuing credentials, reading
recipients, and public verification.

The SDK is a thin, dependency-free client:

- PHP 7.4+ (also runs on PHP 8.x)
- No required Composer runtime dependencies beyond `ext-json`
- Uses `ext-curl` by default, or any PSR-18 client through `Psr18Transport`
- Typed value objects for the resources it models, with the raw payload always
  available through `toArray()`
- Typed `ApiException` for RFC 7807 problem responses
- An OpenAPI drift test that fails when the API moves underneath the SDK

The Verifycate service is a hosted, paid product. This SDK is the open client
used by the WordPress plugin and other integrations.

## Installation

The package is distributed from GitHub. Add the repository and require it:

```json
{
  "repositories": [
    { "type": "vcs", "url": "https://github.com/verifycate/php-sdk" }
  ],
  "require": {
    "verifycate/php-sdk": "^0.1"
  }
}
```

```bash
composer require verifycate/php-sdk:^0.1
```

## Quick start

```php
use Verifycate\Sdk\Client;
use Verifycate\Sdk\Config;

$client = new Client(new Config(
    'vc_live_...',   // workspace API key
    'acme'           // workspace slug
));

// Certificates available for mapping
$page = $client->certificates()->list('Leadership');
foreach ($page->getItems() as $certificate) {
    echo $certificate->getId() . ' ' . $certificate->getName() . PHP_EOL;
}

// Queue a recipient
$result = $client->issues()->import($certificateId, [
    [
        'email' => 'ada@example.com',
        'name' => 'Ada Lovelace',
        'attributes' => ['course_hours' => '40'],
    ],
]);

// Release the queued issue
$issue = $result->getFirstIssue();
if ($issue !== null && $issue->isPending()) {
    $issue = $client->issues()->issue($issue->getId());
}

echo $issue->getPublicUrl();
```

On PHP 8.0+ every resource method also accepts named arguments:

```php
$page = $client->certificates()->list(search: 'Leadership', limit: 100);
```

## Connect with only an API key

The workspace slug is optional in `Config`. Resolve it from the key first, then
rebind the client:

```php
use Verifycate\Sdk\Client;
use Verifycate\Sdk\Config;

$client = new Client(new Config('vc_live_...'));

$workspace = $client->workspace()->current();
if (! $workspace->canWriteCertificates()) {
    throw new RuntimeException('This API key cannot issue certificates.');
}

$client = $client->withWorkspace($workspace->getSlug());
```

`workspace()->current()` returns the slug, name, seat status, scopes and whether
the key has full workspace access.

## Resources

| Accessor | What it covers |
| --- | --- |
| `certificates()` | List, get, create, update and delete certificates |
| `issues()` | List issues for a certificate, import recipients, get/update/delete an issue, issue, reissue, resend an unlock form |
| `recipients()` | List recipients across the workspace, fetch one recipient by email with every issue they hold |
| `verification()` | Fetch a public certificate and verify its signature |
| `workspace()` | Resolve the workspace bound to the API key, including its permissions |

Every response object is a typed DTO with status helpers and the complete raw
payload:

```php
$certificate = $client->certificates()->get($id);
$certificate->isActive();
$certificate->getAttributes();   // attribute definitions
$certificate->toArray();         // untouched API payload
```

## Transport

`CurlTransport` is the default and needs no dependencies:

```php
use Verifycate\Sdk\Http\CurlTransport;

$client = new Client($config, new CurlTransport([
    CURLOPT_PROXY => 'http://proxy.example.com:3128',
]));
```

Any PSR-18 client works through `Psr18Transport`:

```php
use Verifycate\Sdk\Http\Psr18Transport;

$client = new Client($config, new Psr18Transport($httpClient, $requestFactory, $streamFactory));
```

Hosts with their own HTTP stack implement `TransportInterface` and return a
`Response`. The WordPress plugin uses this to route requests through
`wp_remote_request`, so the site's proxy, TLS and timeout policy still apply.

## Errors

Non-2xx responses throw `Verifycate\Sdk\Exception\ApiException`:

```php
use Verifycate\Sdk\Exception\ApiException;

try {
    $client->issues()->issue($issueId);
} catch (ApiException $exception) {
    if ($exception->isValidationError()) {
        // 422: show getDetail() / getErrors() to the user
    }

    if ($exception->isRetryable()) {
        // 429 or 5xx: retry later, honour getRetryAfter() when present
    }

    // 400/401/402/403/404/409 are permanent until the request changes
}
```

Network failures throw `Verifycate\Sdk\Exception\TransportException`. Invalid
JSON throws `Verifycate\Sdk\Exception\InvalidResponseException`. Missing
configuration throws `ConfigurationException`.

## Public verification

The public endpoints do not need an API key:

```php
$public = $client->verification()->get($publicId);
$result = $client->verification()->verify($publicId);

if ($result->isValid()) {
    echo $public->getRecipientName() . ' holds ' . $public->getCertificateName();
}
```

## OpenAPI drift

`openapi/openapi.json` is a snapshot of the public document served at
`https://api.verifycate.com/api/v1/openapi.json`. The SDK is hand-written for
ergonomics, and `tests/Spec/SpecDriftTest.php` keeps it honest: every operation,
method, path, API-key policy, query parameter, request schema and modeled DTO
field must still exist in the snapshot.

Refresh the snapshot when the API changes:

```bash
composer sync-spec https://api.verifycate.com/api/v1/openapi.json
# or
php scripts/sync-spec.php /path/to/openapi.json
composer check
```

The `Spec sync` workflow runs the same check weekly and fails when the live
document and the committed snapshot diverge.

## Development

```bash
composer install
composer lint       # PSR-12
composer analyse    # PHPStan level 8, PHP 7.4 semantics
composer test       # PHPUnit
composer check      # all of the above
```

`composer.json` pins the platform to PHP 7.4.33 so dependency resolution stays
compatible with the oldest supported runtime. CI runs the suite on PHP 7.4
through 8.4.

## License

[MIT](LICENSE).
