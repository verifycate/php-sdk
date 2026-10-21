<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Tests\Conformance;

use PHPUnit\Framework\TestCase;
use Verifycate\Sdk\Exception\ApiException;
use Verifycate\Sdk\Operations;
use Verifycate\Sdk\Tests\Support\ClientFactory;
use Verifycate\Sdk\Tests\Support\Fixtures;
use Verifycate\Sdk\Tests\Support\MockTransport;

/**
 * Recorded error responses every Verifycate client must map identically.
 */
final class ErrorMappingTest extends TestCase
{
    /**
     * @return array<string, array{int}>
     */
    public static function problemProvider(): array
    {
        return [
            'validation' => [400],
            'unauthorized' => [401],
            'payment required' => [402],
            'forbidden' => [403],
            'not found' => [404],
            'unprocessable' => [422],
            'rate limited' => [429],
            'server error' => [500],
        ];
    }

    /**
     * @dataProvider problemProvider
     */
    public function testMapsAProblemResponse(int $status): void
    {
        $transport = new MockTransport();
        $transport->queue($status, Fixtures::problem($status));
        $client = ClientFactory::make($transport);

        try {
            $client->request(Operations::LIST_CERTIFICATES, ['slug' => 'acme']);
            $this->fail('Expected an ApiException.');
        } catch (ApiException $exception) {
            $problem = Fixtures::problem($status);
            $this->assertSame($status, $exception->getStatusCode());
            $this->assertSame($problem['code'], $exception->getErrorCode());
            $this->assertSame($problem['title'], $exception->getTitle());
            $this->assertSame($problem['detail'], $exception->getDetail());
        }
    }

    public function testMapsARateLimitRetryAfterHeader(): void
    {
        $transport = new MockTransport();
        $transport->queue(429, Fixtures::problem(429), ['retry-after' => ['60']]);
        $client = ClientFactory::make($transport);

        try {
            $client->request(Operations::LIST_CERTIFICATES, ['slug' => 'acme']);
            $this->fail('Expected an ApiException.');
        } catch (ApiException $exception) {
            $this->assertSame(60, $exception->getRetryAfter());
            $this->assertTrue($exception->isRateLimited());
            $this->assertTrue($exception->isRetryable());
        }
    }

    public function testMapsATransportFailure(): void
    {
        $transport = new MockTransport();
        $client = ClientFactory::make($transport);

        $this->expectException(\Verifycate\Sdk\Exception\TransportException::class);
        $client->request(Operations::LIST_CERTIFICATES, ['slug' => 'acme']);
    }
}
