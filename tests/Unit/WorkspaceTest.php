<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Verifycate\Sdk\Client;
use Verifycate\Sdk\Config;
use Verifycate\Sdk\Tests\Support\ClientFactory;
use Verifycate\Sdk\Tests\Support\Fixtures;
use Verifycate\Sdk\Tests\Support\MockTransport;

final class WorkspaceTest extends TestCase
{
    public function testResolvesTheWorkspaceFromTheApiKey(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, Fixtures::json('current-workspace'));
        $client = new Client(new Config('vc_test_key', null, 'https://api.verifycate.test'), $transport);

        $workspace = $client->workspace()->current();

        $this->assertSame('acme', $workspace->getSlug());
        $this->assertSame('Acme Academy', $workspace->getName());
        $this->assertSame('Acme Academy', $workspace->getIssuerName());
        $this->assertTrue($workspace->isSeatLive());
        $this->assertFalse($workspace->hasFullAccess());
        $this->assertTrue($workspace->canReadCertificates());
        $this->assertTrue($workspace->canWriteCertificates());
        $this->assertSame(
            'https://api.verifycate.test/api/v1/workspaces/current',
            $transport->getLastRequest()->getUrl()
        );
        $this->assertSame('Bearer vc_test_key', $transport->getLastRequest()->getHeaders()['Authorization']);
    }

    public function testFullAccessKeysCanReadAndWrite(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, [
            'slug' => 'acme',
            'name' => 'Acme Academy',
            'issuer_name' => 'Acme Academy',
            'seat_live' => true,
            'permissions' => [],
            'full_access' => true,
        ]);
        $client = ClientFactory::make($transport);

        $workspace = $client->workspace()->current();

        $this->assertTrue($workspace->hasFullAccess());
        $this->assertSame([], $workspace->getPermissions());
        $this->assertTrue($workspace->canReadCertificates());
        $this->assertTrue($workspace->canWriteCertificates());
    }

    public function testReadOnlyKeysCannotWrite(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, [
            'slug' => 'acme',
            'name' => 'Acme Academy',
            'issuer_name' => 'Acme Academy',
            'seat_live' => true,
            'permissions' => ['certificates:read'],
            'full_access' => false,
        ]);
        $client = ClientFactory::make($transport);

        $workspace = $client->workspace()->current();

        $this->assertTrue($workspace->canReadCertificates());
        $this->assertFalse($workspace->canWriteCertificates());
    }

    public function testClientCanBeReboundAfterResolvingTheWorkspace(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, Fixtures::json('current-workspace'));
        $client = new Client(new Config('vc_test_key', null, 'https://api.verifycate.test'), $transport);

        $workspace = $client->workspace()->current();
        $bound = $client->withWorkspace($workspace->getSlug());

        $this->assertSame('acme', $bound->getConfig()->getWorkspace());
    }
}
