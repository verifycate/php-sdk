<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Tests\Support;

use Verifycate\Sdk\Exception\TransportException;
use Verifycate\Sdk\Http\Request;
use Verifycate\Sdk\Http\Response;
use Verifycate\Sdk\Http\TransportInterface;

final class MockTransport implements TransportInterface
{
    /** @var array<int, Response> */
    private array $responses;

    /** @var array<int, Request> */
    private array $requests = [];

    /**
     * @param array<int, Response> $responses
     */
    public function __construct(array $responses = [])
    {
        $this->responses = $responses;
    }

    /**
     * @param array<string, mixed>|string $body
     * @param array<string, array<int, string>> $headers
     */
    public function queue(int $statusCode, $body = [], array $headers = []): self
    {
        if (is_array($body)) {
            $body = json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        }

        $this->responses[] = new Response($statusCode, $headers, (string) $body);

        return $this;
    }

    public function send(Request $request): Response
    {
        $this->requests[] = $request;

        $response = array_shift($this->responses);
        if ($response === null) {
            throw new TransportException('No response was queued for this request.');
        }

        return $response;
    }

    /**
     * @return array<int, Request>
     */
    public function getRequests(): array
    {
        return $this->requests;
    }

    public function getLastRequest(): Request
    {
        $requests = $this->requests;
        if ($requests === []) {
            throw new \RuntimeException('No request has been sent.');
        }

        return $requests[count($requests) - 1];
    }
}
