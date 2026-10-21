<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Verifycate\Sdk\Client;
use Verifycate\Sdk\Config;
use Verifycate\Sdk\Exception\ApiException;
use Verifycate\Sdk\Exception\ConfigurationException;
use Verifycate\Sdk\Exception\InvalidResponseException;
use Verifycate\Sdk\Operations;
use Verifycate\Sdk\Tests\Support\ClientFactory;
use Verifycate\Sdk\Tests\Support\Fixtures;
use Verifycate\Sdk\Tests\Support\MockTransport;

final class ClientTest extends TestCase
{
    public function testSendsAnAuthenticatedGetWithEncodedQueryParameters(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, ['ok' => true]);
        $client = ClientFactory::make($transport);

        $result = $client->request(
            Operations::LIST_CERTIFICATES,
            ['slug' => 'acme'],
            ['search' => 'lead ership', 'limit' => 50, 'offset' => 0, 'empty' => null]
        );

        $this->assertSame(['ok' => true], $result);

        $request = $transport->getLastRequest();
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame(
            'https://api.verifycate.test/api/v1/workspaces/acme/certificates?search=lead%20ership&limit=50&offset=0',
            $request->getUrl()
        );
        $this->assertSame('Bearer vc_test_key', $request->getHeaders()['Authorization']);
        $this->assertNull($request->getBody());
        $this->assertSame($request, $client->getLastRequest());
    }

    public function testEncodesPathParametersAndPostsJson(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, []);
        $client = ClientFactory::make($transport);

        $client->request(
            Operations::IMPORT_ISSUES,
            ['slug' => 'acme', 'id' => 'cert id'],
            [],
            ['issues' => []]
        );

        $request = $transport->getLastRequest();
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame(
            'https://api.verifycate.test/api/v1/workspaces/acme/certificates/cert%20id/issues',
            $request->getUrl()
        );
        $this->assertSame('application/json', $request->getHeaders()['Content-Type']);
        $this->assertSame('{"issues":[]}', $request->getBody());
    }

    public function testOmitsTheAuthorizationHeaderForAnonymousClients(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, ['ok' => true]);
        $client = new Client(new Config('', 'acme', 'https://api.verifycate.test'), $transport);

        $client->request(Operations::LIST_CERTIFICATES, ['slug' => 'acme']);

        $this->assertArrayNotHasKey('Authorization', $transport->getLastRequest()->getHeaders());
    }

    public function testMapsProblemDetailsToApiException(): void
    {
        $transport = new MockTransport();
        $transport->queue(422, Fixtures::problem(422));
        $client = ClientFactory::make($transport);

        try {
            $client->request(Operations::IMPORT_ISSUES, ['slug' => 'acme', 'id' => 'cert'], [], ['issues' => []]);
            $this->fail('Expected an ApiException.');
        } catch (ApiException $exception) {
            $this->assertSame(422, $exception->getStatusCode());
            $this->assertSame('recipient_validation_failed', $exception->getErrorCode());
            $this->assertSame('Unprocessable Entity', $exception->getTitle());
            $this->assertSame('The recipient could not be validated.', $exception->getDetail());
            $this->assertCount(1, $exception->getErrors());
            $this->assertArrayHasKey('summary', $exception->getData());
            $this->assertFalse($exception->isRetryable());
        }
    }

    public function testSurfacesANonJsonErrorBody(): void
    {
        $transport = new MockTransport();
        $transport->queue(500, '<html>upstream exploded</html>');
        $client = ClientFactory::make($transport);

        try {
            $client->request(Operations::LIST_CERTIFICATES, ['slug' => 'acme']);
            $this->fail('Expected an ApiException.');
        } catch (ApiException $exception) {
            $this->assertSame(500, $exception->getStatusCode());
            $this->assertSame('HTTP 500', $exception->getTitle());
            $this->assertStringContainsString('upstream exploded', $exception->getDetail());
            $this->assertTrue($exception->isRetryable());
        }
    }

    public function testRejectsANonJsonSuccessBody(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, 'not-json');
        $client = ClientFactory::make($transport);

        $this->expectException(InvalidResponseException::class);
        $client->request(Operations::LIST_CERTIFICATES, ['slug' => 'acme']);
    }

    public function testReturnsAnEmptyArrayForNoContent(): void
    {
        $transport = new MockTransport();
        $transport->queue(204, '');
        $client = ClientFactory::make($transport);

        $this->assertSame([], $client->request(Operations::DELETE_CERTIFICATE, ['slug' => 'acme', 'id' => 'cert']));
    }

    public function testRejectsAMissingPathParameter(): void
    {
        $transport = new MockTransport();
        $client = ClientFactory::make($transport);

        $this->expectException(ConfigurationException::class);
        $client->request(Operations::GET_CERTIFICATE, ['slug' => 'acme']);
    }

    public function testNormalisesHeaderCaseWhenReadingRetryAfter(): void
    {
        $transport = new MockTransport();
        $transport->queue(429, Fixtures::problem(429), ['retry-after' => ['30']]);
        $client = ClientFactory::make($transport);

        try {
            $client->request(Operations::LIST_CERTIFICATES, ['slug' => 'acme']);
            $this->fail('Expected an ApiException.');
        } catch (ApiException $exception) {
            $this->assertTrue($exception->isRateLimited());
            $this->assertSame(30, $exception->getRetryAfter());
        }
    }
}
