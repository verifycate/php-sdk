<?php

declare(strict_types=1);

namespace Verifycate\Sdk;

use Verifycate\Sdk\Exception\ConfigurationException;

final class Config
{
    public const DEFAULT_BASE_URL = 'https://api.verifycate.com';

    private string $apiKey;

    private ?string $workspace;

    private string $baseUrl;

    private float $timeout;

    private string $userAgent;

    public function __construct(
        string $apiKey,
        ?string $workspace = null,
        string $baseUrl = self::DEFAULT_BASE_URL,
        float $timeout = 30.0,
        ?string $userAgent = null
    ) {
        $apiKey = trim($apiKey);
        $workspace = $workspace === null ? null : trim($workspace);
        $baseUrl = rtrim(trim($baseUrl), '/');

        if ($baseUrl === '' || preg_match('#^https?://#i', $baseUrl) !== 1) {
            throw new ConfigurationException('The API base URL must be an absolute http or https URL.');
        }

        if ($timeout <= 0) {
            throw new ConfigurationException('The HTTP timeout must be greater than zero.');
        }

        $this->apiKey = $apiKey;
        $this->workspace = $workspace === '' ? null : $workspace;
        $this->baseUrl = $baseUrl;
        $this->timeout = $timeout;
        $this->userAgent = $userAgent ?? sprintf('verifycate-php-sdk/0.1.0 (PHP %s)', PHP_VERSION);
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function hasApiKey(): bool
    {
        return $this->apiKey !== '';
    }

    public function getWorkspace(): ?string
    {
        return $this->workspace;
    }

    public function requireWorkspace(): string
    {
        if ($this->workspace === null) {
            throw new ConfigurationException(
                'This operation needs a workspace slug. Pass one to the Config constructor.'
            );
        }

        return $this->workspace;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getTimeout(): float
    {
        return $this->timeout;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    /**
     * Return a copy of this configuration with a different workspace slug.
     */
    public function withWorkspace(?string $workspace): self
    {
        $clone = clone $this;
        $workspace = $workspace === null ? null : trim($workspace);
        $clone->workspace = $workspace === '' || $workspace === null ? null : $workspace;

        return $clone;
    }
}
