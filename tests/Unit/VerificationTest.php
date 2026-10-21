<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Verifycate\Sdk\Tests\Support\ClientFactory;
use Verifycate\Sdk\Tests\Support\Fixtures;
use Verifycate\Sdk\Tests\Support\MockTransport;

final class VerificationTest extends TestCase
{
    public function testGetsAPublicCertificate(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, Fixtures::json('public-certificate'));
        $client = ClientFactory::make($transport);

        $certificate = $client->verification()->get('7c1f0a5e-0f2b-4a1c-9f4d-2b3c4d5e6f70');

        $this->assertTrue($certificate->isActive());
        $this->assertSame('Leadership Essentials', $certificate->getCertificateName());
        $this->assertSame('course', $certificate->getCertificateType());
        $this->assertSame('https://cdn.example.com/certificates/abc.png', $certificate->getPngUrl());
        $this->assertSame('https://cdn.example.com/certificates/abc.pdf', $certificate->getPdfUrl());
        $this->assertSame(
            'https://verifycate.com/c/7c1f0a5e-0f2b-4a1c-9f4d-2b3c4d5e6f70',
            $certificate->getVerificationUrl()
        );
        $this->assertStringContainsString('workspace=acme', $transport->getLastRequest()->getUrl());
    }

    public function testExplicitWorkspaceOverridesTheConfiguredOne(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, Fixtures::json('public-certificate'));
        $client = ClientFactory::make($transport);

        $client->verification()->get('public-id', 'other-workspace');

        $this->assertStringContainsString('workspace=other-workspace', $transport->getLastRequest()->getUrl());
    }

    public function testVerifiesASignature(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, Fixtures::json('verify-valid'));
        $client = ClientFactory::make($transport);

        $result = $client->verification()->verify('public-id');

        $this->assertTrue($result->isValid());
        $this->assertNull($result->getReason());
        $this->assertNotNull($result->getCheckedAt());
        $this->assertStringEndsWith(
            '/certificates/public-id/verify?workspace=acme',
            $transport->getLastRequest()->getUrl()
        );
    }

    public function testRejectsAnEmptyPublicId(): void
    {
        $transport = new MockTransport();
        $client = ClientFactory::make($transport);

        $this->expectException(\InvalidArgumentException::class);
        $client->verification()->get(' ');
    }
}
