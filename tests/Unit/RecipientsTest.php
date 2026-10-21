<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Verifycate\Sdk\Tests\Support\ClientFactory;
use Verifycate\Sdk\Tests\Support\Fixtures;
use Verifycate\Sdk\Tests\Support\MockTransport;

final class RecipientsTest extends TestCase
{
    public function testGetsARecipientByEmail(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, Fixtures::json('recipient-detail'));
        $client = ClientFactory::make($transport);

        $recipient = $client->recipients()->get('ada@example.com');

        $this->assertSame('ada@example.com', $recipient->getEmail());
        $this->assertSame('Ada Lovelace', $recipient->getName());
        $this->assertSame(1, $recipient->getIssueCount());
        $this->assertCount(1, $recipient->getIssues());
        $this->assertCount(1, $recipient->getActiveIssues());
        $this->assertSame(
            'https://api.verifycate.test/api/v1/workspaces/acme/recipients/ada%40example.com',
            $transport->getLastRequest()->getUrl()
        );
    }

    public function testRejectsAnInvalidEmail(): void
    {
        $transport = new MockTransport();
        $client = ClientFactory::make($transport);

        $this->expectException(\InvalidArgumentException::class);
        $client->recipients()->get('not-an-email');
    }

    public function testListsRecipients(): void
    {
        $transport = new MockTransport();
        $transport->queue(200, [
            'items' => [
                [
                    'recipient_email' => 'ada@example.com',
                    'recipient_name' => 'Ada Lovelace',
                    'issue_count' => 1,
                    'certificate_count' => 1,
                    'latest_created_at' => '2026-01-18T09:00:00Z',
                    'issues' => [],
                ],
            ],
            'total' => 1,
            'limit' => 25,
            'offset' => 50,
        ]);
        $client = ClientFactory::make($transport);

        $page = $client->recipients()->list('ada', null, 'cert-1', 25, 50);

        $this->assertSame(1, $page->count());
        $this->assertSame(1, $page->getTotal());
        $this->assertSame('Ada Lovelace', $page->getItems()[0]->getName());

        $url = $transport->getLastRequest()->getUrl();
        $this->assertStringContainsString('search=ada', $url);
        $this->assertStringContainsString('certificate=cert-1', $url);
        $this->assertStringContainsString('limit=25', $url);
        $this->assertStringContainsString('offset=50', $url);
    }
}
