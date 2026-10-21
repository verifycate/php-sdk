<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Http;

use Verifycate\Sdk\Exception\InvalidResponseException;

final class Response
{
    private int $statusCode;

    /** @var array<string, array<int, string>> */
    private array $headers;

    private string $body;

    /**
     * @param array<string, array<int, string>> $headers Header names are lower-cased.
     */
    public function __construct(int $statusCode, array $headers, string $body)
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->body = $body;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getHeader(string $name): ?string
    {
        $name = strtolower($name);
        $values = $this->headers[$name] ?? [];

        return $values === [] ? null : (string) $values[0];
    }

    /**
     * @return array<string, mixed>
     */
    public function json(): array
    {
        if (trim($this->body) === '') {
            return [];
        }

        try {
            $decoded = json_decode($this->body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new InvalidResponseException(
                'The Verifycate API returned a response that is not valid JSON.',
                0,
                $exception
            );
        }

        if (!is_array($decoded)) {
            throw new InvalidResponseException('The Verifycate API returned an unexpected JSON payload.');
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }
}
