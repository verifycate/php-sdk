<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Tests\Support;

final class Fixtures
{
    /**
     * @return array<string, mixed>
     */
    public static function json(string $name): array
    {
        $path = dirname(__DIR__) . '/fixtures/responses/' . $name . '.json';
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new \RuntimeException(sprintf('Missing fixture %s.', $path));
        }

        $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            throw new \RuntimeException(sprintf('Fixture %s is not a JSON object.', $path));
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    /**
     * @return array<string, mixed>
     */
    public static function problem(int $statusCode): array
    {
        $problems = self::json('problems');
        $problem = $problems[(string) $statusCode] ?? null;
        if (!is_array($problem)) {
            throw new \RuntimeException(sprintf('No problem fixture for status %d.', $statusCode));
        }

        /** @var array<string, mixed> $problem */
        return $problem;
    }
}
