<?php

/**
 * Replace the committed OpenAPI snapshot with a fresh public document.
 *
 * Usage:
 *   php scripts/sync-spec.php /path/to/openapi.json
 *   php scripts/sync-spec.php https://api.verifycate.com/api/v1/openapi.json
 *   VERIFYCATE_OPENAPI=/path/to/openapi.json composer sync-spec
 */

declare(strict_types=1);

$source = $argv[1] ?? getenv('VERIFYCATE_OPENAPI') ?: '';

if (!is_string($source) || trim($source) === '') {
    fwrite(STDERR, "Usage: php scripts/sync-spec.php <path-or-url>\n");
    exit(1);
}

$source = trim($source);
$contents = file_get_contents($source);

if ($contents === false) {
    fwrite(STDERR, sprintf("Unable to read %s.\n", $source));
    exit(1);
}

$document = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

if (!is_array($document) || !isset($document['openapi']) || !isset($document['info']['title'])) {
    fwrite(STDERR, "The source is not an OpenAPI document.\n");
    exit(1);
}

$target = dirname(__DIR__) . '/openapi/openapi.json';
$encoded = json_encode(
    $document,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
);

if (file_put_contents($target, $encoded . "\n") === false) {
    fwrite(STDERR, sprintf("Unable to write %s.\n", $target));
    exit(1);
}

fwrite(STDOUT, sprintf("Wrote %s from %s.\n", $target, $source));
