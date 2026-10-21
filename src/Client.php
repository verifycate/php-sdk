<?php

declare(strict_types=1);

namespace Verifycate\Sdk;

use Verifycate\Sdk\Exception\ApiException;
use Verifycate\Sdk\Exception\ConfigurationException;
use Verifycate\Sdk\Http\CurlTransport;
use Verifycate\Sdk\Http\Request;
use Verifycate\Sdk\Http\TransportInterface;
use Verifycate\Sdk\Resources\Certificates;
use Verifycate\Sdk\Resources\Issues;
use Verifycate\Sdk\Resources\Recipients;
use Verifycate\Sdk\Resources\Verification;

final class Client
{
    private Config $config;

    private TransportInterface $transport;

    private Certificates $certificates;

    private Issues $issues;

    private Recipients $recipients;

    private Verification $verification;

    private ?Request $lastRequest = null;

    public function __construct(Config $config, ?TransportInterface $transport = null)
    {
        $this->config = $config;
        $this->transport = $transport ?? new CurlTransport();
        $this->certificates = new Certificates($this);
        $this->issues = new Issues($this);
        $this->recipients = new Recipients($this);
        $this->verification = new Verification($this);
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function certificates(): Certificates
    {
        return $this->certificates;
    }

    public function issues(): Issues
    {
        return $this->issues;
    }

    public function recipients(): Recipients
    {
        return $this->recipients;
    }

    public function verification(): Verification
    {
        return $this->verification;
    }

    /**
     * The most recent HTTP request, useful for logging and diagnostics.
     */
    public function getLastRequest(): ?Request
    {
        return $this->lastRequest;
    }

    /**
     * Send one API operation.
     *
     * @param array<string, mixed> $operation One entry from Operations::all().
     * @param array<string, string|int> $path Path parameter values.
     * @param array<string, mixed> $query Query parameters; null and empty values are dropped.
     * @param array<string, mixed>|null $body JSON request body.
     * @param array<string, string> $headers Extra request headers.
     * @return array<string, mixed>
     *
     * @throws ApiException
     */
    public function request(
        array $operation,
        array $path = [],
        array $query = [],
        ?array $body = null,
        array $headers = []
    ): array {
        $method = isset($operation['method']) ? (string) $operation['method'] : '';
        $template = isset($operation['path']) ? (string) $operation['path'] : '';

        if ($method === '' || $template === '') {
            throw new ConfigurationException('Every SDK operation needs a method and a path template.');
        }

        $resolvedPath = $template;
        foreach ($path as $name => $value) {
            $resolvedPath = str_replace('{' . $name . '}', rawurlencode((string) $value), $resolvedPath);
        }

        if (preg_match('/\{[^}]+\}/', $resolvedPath) === 1) {
            throw new ConfigurationException(
                sprintf('Missing path parameter for %s %s.', $method, $template)
            );
        }

        $query = array_filter(
            $query,
            static function ($value): bool {
                return $value !== null && $value !== '';
            }
        );

        $url = $this->config->getBaseUrl() . $resolvedPath;
        if ($query !== []) {
            $url .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        }

        $requestHeaders = [
            'Accept' => 'application/json, application/problem+json',
            'User-Agent' => $this->config->getUserAgent(),
        ];

        if ($this->config->hasApiKey()) {
            $requestHeaders['Authorization'] = 'Bearer ' . $this->config->getApiKey();
        }

        $payload = null;
        if ($body !== null) {
            try {
                $payload = json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            } catch (\JsonException $exception) {
                throw new ConfigurationException('Unable to encode the request body as JSON.', 0, $exception);
            }

            $requestHeaders['Content-Type'] = 'application/json';
        }

        $requestHeaders = array_merge($requestHeaders, $headers);
        $request = new Request($method, $url, $requestHeaders, $payload, $this->config->getTimeout());
        $this->lastRequest = $request;

        $response = $this->transport->send($request);
        $statusCode = $response->getStatusCode();

        if ($statusCode >= 200 && $statusCode < 300) {
            if ($statusCode === 204 || trim($response->getBody()) === '') {
                return [];
            }

            return $response->json();
        }

        $problem = null;
        try {
            $decoded = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
            if (is_array($decoded)) {
                /** @var array<string, mixed> $problem */
                $problem = $decoded;
            }
        } catch (\JsonException $exception) {
            // Fall through: a non-JSON error body is still surfaced below.
        }

        if ($problem === null) {
            $problem = [
                'title' => sprintf('HTTP %d', $statusCode),
                'detail' => trim(substr($response->getBody(), 0, 500)),
            ];
        }

        throw ApiException::fromResponse($statusCode, $problem, $response->getHeaders());
    }
}
