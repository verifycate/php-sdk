<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Http;

interface TransportInterface
{
    /**
     * Send one HTTP request.
     *
     * Transports must not throw on 4xx or 5xx responses; the client maps those
     * to ApiException. They only throw TransportException when no response was
     * received at all.
     *
     * @throws \Verifycate\Sdk\Exception\TransportException
     */
    public function send(Request $request): Response;
}
