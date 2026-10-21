<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Http;

final class Request
{
    private string $method;

    private string $url;

    /** @var array<string, string> */
    private array $headers;

    private ?string $body;

    private float $timeout;

    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        string $method,
        string $url,
        array $headers = [],
        ?string $body = null,
        float $timeout = 30.0
    ) {
        $this->method = strtoupper($method);
        $this->url = $url;
        $this->headers = $headers;
        $this->body = $body;
        $this->timeout = $timeout;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function getTimeout(): float
    {
        return $this->timeout;
    }
}
