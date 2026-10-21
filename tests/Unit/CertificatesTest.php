<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Verifycate\Sdk\Tests\Support\ClientFactory;
use Verifycate\Sdk\Tests\Support\Fixtures;
use Verifycate\Sdk\Tests\Support\MockTransport;

final class CertificatesTest extends TestCase
{
    public function testListsCertificates(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, Fixtures::json('certificate-list'));
        $client = ClientFactory::make($transport);

        $page = $client->certificates()->list('lead', 10, 20);

        $this->assertSame(1, $page->count());
        $this->assertSame(1, $page->getTotal());
        $this->assertSame('Leadership Essentials', $page->getItems()[0]->getName());
        $this->assertSame(
            'https://api.verifycate.test/api/v1/workspaces/acme/certificates?search=lead&limit=10&offset=20',
            $transport->getLastRequest()->getUrl()
        );
    }

    public function testRejectsAnInvalidPageSize(): void
    {
        $transport = new MockTransport();
        $client = ClientFactory::make($transport);

        $this->expectException(\InvalidArgumentException::class);
        $client->certificates()->list(null, 0);
    }

    public function testGetsACertificate(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, Fixtures::json('certificate-detail'));
        $client = ClientFactory::make($transport);

        $certificate = $client->certificates()->get('8d7f9c1e-3a2b-4c5d-8e9f-0a1b2c3d4e5f');

        $this->assertSame('Leadership Essentials', $certificate->getName());
        $this->assertTrue($certificate->isActive());
        $this->assertSame('course', $certificate->getCertificateType());
        $this->assertSame(365, $certificate->getValidityDays());
        $this->assertSame('course_hours', $certificate->getAttributes()[0]['key']);
        $this->assertSame('intermediate', $certificate->getLevel());
        $this->assertCount(2, $certificate->getEarningCriteria());
    }

    public function testCreatesACertificate(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, Fixtures::json('certificate-detail'));
        $client = ClientFactory::make($transport);

        $client->certificates()->create(['name' => 'Leadership Essentials']);

        $request = $transport->getLastRequest();
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('{"name":"Leadership Essentials"}', $request->getBody());
    }

    public function testCreateRequiresAName(): void
    {
        $transport = new MockTransport();
        $client = ClientFactory::make($transport);

        $this->expectException(\InvalidArgumentException::class);
        $client->certificates()->create(['description' => 'No name']);
    }

    public function testUpdateRequiresAtLeastOneField(): void
    {
        $transport = new MockTransport();
        $client = ClientFactory::make($transport);

        $this->expectException(\InvalidArgumentException::class);
        $client->certificates()->update('cert', []);
    }

    public function testDeletesACertificate(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, ['ok' => true]);
        $client = ClientFactory::make($transport);

        $this->assertTrue($client->certificates()->delete('cert'));

        $request = $transport->getLastRequest();
        $this->assertSame('DELETE', $request->getMethod());
        $this->assertSame('https://api.verifycate.test/api/v1/workspaces/acme/certificates/cert', $request->getUrl());
    }
}
