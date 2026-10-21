<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Verifycate\Sdk\Tests\Support\ClientFactory;
use Verifycate\Sdk\Tests\Support\Fixtures;
use Verifycate\Sdk\Tests\Support\MockTransport;

final class IssuesTest extends TestCase
{
    public function testImportsRecipientsAndParsesTheResult(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, Fixtures::json('import-issues'));
        $client = ClientFactory::make($transport);

        $result = $client->issues()->import('cert-1', [
            ['email' => 'ada@example.com', 'name' => 'Ada Lovelace', 'attributes' => ['course_hours' => '40']],
        ]);

        $firstIssue = $result->getFirstIssue();
        $this->assertNotNull($firstIssue);
        $this->assertSame('active', $firstIssue->getStatus());
        $this->assertSame('7c1f0a5e-0f2b-4a1c-9f4d-2b3c4d5e6f70', $firstIssue->getPublicId());
        $this->assertSame(['grace@example.com'], $result->getRejected());
        $this->assertTrue($result->hasRejections());
        $this->assertSame(2, $result->getSummary()['total']);
        $this->assertCount(2, $result->getResults());

        $request = $transport->getLastRequest();
        $this->assertSame(
            'https://api.verifycate.test/api/v1/workspaces/acme/certificates/cert-1/issues',
            $request->getUrl()
        );
        $body = json_decode((string) $request->getBody(), true);
        $this->assertSame('ada@example.com', $body['issues'][0]['email']);
        $this->assertArrayNotHasKey('source', $body);
    }

    public function testImportsWithAnExplicitSource(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, Fixtures::json('import-issues'));
        $client = ClientFactory::make($transport);

        $client->issues()->import('cert-1', [['email' => 'ada@example.com']], 'manual');

        $body = json_decode((string) $transport->getLastRequest()->getBody(), true);
        $this->assertSame('manual', $body['source']);
    }

    public function testImportRequiresRecipients(): void
    {
        $transport = new MockTransport();
        $client = ClientFactory::make($transport);

        $this->expectException(\InvalidArgumentException::class);
        $client->issues()->import('cert-1', []);
    }

    public function testImportRequiresAnEmailPerRecipient(): void
    {
        $transport = new MockTransport();
        $client = ClientFactory::make($transport);

        $this->expectException(\InvalidArgumentException::class);
        $client->issues()->import('cert-1', [['name' => 'Ada']]);
    }

    public function testImportRejectsAnUnknownSource(): void
    {
        $transport = new MockTransport();
        $client = ClientFactory::make($transport);

        $this->expectException(\InvalidArgumentException::class);
        $client->issues()->import('cert-1', [['email' => 'ada@example.com']], 'dashboard');
    }

    public function testListsIssuesWithFilters(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, [
            'items' => [Fixtures::json('issue')],
            'total' => 1,
            'limit' => 25,
            'offset' => 0,
        ]);
        $client = ClientFactory::make($transport);

        $page = $client->issues()->listForCertificate('cert-1', ['status' => 'active'], 25);

        $this->assertSame(1, $page->count());
        $this->assertSame('active', $page->getItems()[0]->getStatus());
        $this->assertStringContainsString('status=active', $transport->getLastRequest()->getUrl());
        $this->assertStringContainsString('limit=25', $transport->getLastRequest()->getUrl());
    }

    public function testRejectsAnUnknownIssueFilter(): void
    {
        $transport = new MockTransport();
        $client = ClientFactory::make($transport);

        $this->expectException(\InvalidArgumentException::class);
        $client->issues()->listForCertificate('cert-1', ['sort' => 'name']);
    }

    public function testIssuesAQueuedRecipient(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, Fixtures::json('issue'));
        $client = ClientFactory::make($transport);

        $issue = $client->issues()->issue('2b6c1a44-7f30-4c9e-a1d2-5e8f9b0c3d21');

        $this->assertTrue($issue->isActive());
        $this->assertSame(
            'https://api.verifycate.test/api/v1/workspaces/acme/issues/2b6c1a44-7f30-4c9e-a1d2-5e8f9b0c3d21/issue',
            $transport->getLastRequest()->getUrl()
        );
    }

    public function testReissuesARecipient(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, Fixtures::json('issue'));
        $client = ClientFactory::make($transport);

        $client->issues()->reissue('issue-1');

        $this->assertStringEndsWith('/issues/issue-1/reissue', $transport->getLastRequest()->getUrl());
    }

    public function testRemindsARecipient(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, ['ok' => true]);
        $client = ClientFactory::make($transport);

        $this->assertTrue($client->issues()->remind('issue-1'));
        $this->assertStringEndsWith('/issues/issue-1/remind', $transport->getLastRequest()->getUrl());
    }

    public function testGetsAndDeletesAnIssue(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, Fixtures::json('issue'));
        $transport->queue(200, ['ok' => true]);
        $client = ClientFactory::make($transport);

        $this->assertSame('ada@example.com', $client->issues()->get('issue-1')->getRecipientEmail());
        $this->assertTrue($client->issues()->delete('issue-1'));
    }
}
