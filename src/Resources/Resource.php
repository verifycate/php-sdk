<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Resources;

use Verifycate\Sdk\Client;

abstract class Resource
{
    protected Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    protected function workspace(): string
    {
        return $this->client->getConfig()->requireWorkspace();
    }
}
