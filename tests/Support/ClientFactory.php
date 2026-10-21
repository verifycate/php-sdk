<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Tests\Support;

use Verifycate\Sdk\Client;
use Verifycate\Sdk\Config;

final class ClientFactory
{
    public static function make(
        MockTransport $transport,
        string $apiKey = 'vc_test_key',
        string $workspace = 'acme',
        string $baseUrl = 'https://api.verifycate.test'
    ): Client {
        return new Client(new Config($apiKey, $workspace, $baseUrl, 5.0, 'verifycate-php-sdk-tests'), $transport);
    }
}
