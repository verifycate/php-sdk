<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Http;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Verifycate\Sdk\Exception\TransportException;

/**
 * Bridges the SDK to any PSR-18 client and PSR-17 factories.
 *
 * A WordPress host can supply a PSR-18 implementation backed by
 * wp_remote_request, or use the plugin's native WordPress transport instead.
 */
final class Psr18Transport implements TransportInterface
{
    private ClientInterface $client;

    private RequestFactoryInterface $requestFactory;

    private StreamFactoryInterface $streamFactory;

    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
    }

    public function send(Request $request): Response
    {
        $psrRequest = $this->requestFactory->createRequest($request->getMethod(), $request->getUrl());

        foreach ($request->getHeaders() as $name => $value) {
            $psrRequest = $psrRequest->withHeader($name, $value);
        }

        if ($request->getBody() !== null) {
            $psrRequest = $psrRequest->withBody($this->streamFactory->createStream($request->getBody()));
        }

        try {
            $psrResponse = $this->client->sendRequest($psrRequest);
        } catch (ClientExceptionInterface $exception) {
            throw new TransportException(
                sprintf('Verifycate API request failed: %s', $exception->getMessage()),
                0,
                $exception
            );
        }

        try {
            $body = (string) $psrResponse->getBody();
        } catch (\RuntimeException $exception) {
            throw new TransportException('Unable to read the Verifycate API response body.', 0, $exception);
        }

        /** @var array<string, array<int, string>> $headers */
        $headers = [];
        foreach ($psrResponse->getHeaders() as $name => $values) {
            $headers[strtolower((string) $name)] = array_values(array_map('strval', $values));
        }

        return new Response($psrResponse->getStatusCode(), $headers, $body);
    }
}
