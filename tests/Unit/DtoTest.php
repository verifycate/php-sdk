<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Verifycate\Sdk\Dto\Certificate;
use Verifycate\Sdk\Dto\CertificateIssue;
use Verifycate\Sdk\Dto\ImportResult;
use Verifycate\Sdk\Dto\Recipient;
use Verifycate\Sdk\Dto\RecipientIssue;
use Verifycate\Sdk\Tests\Support\Fixtures;

final class DtoTest extends TestCase
{
    public function testCertificateExposesStatusHelpers(): void
    {
        $certificate = Certificate::fromArray(['id' => 'a', 'name' => 'Draft', 'status' => 'draft']);

        $this->assertTrue($certificate->isDraft());
        $this->assertFalse($certificate->isActive());
        $this->assertSame('a', $certificate->getId());
        $this->assertNull($certificate->getValidityDays());
        $this->assertSame([], $certificate->getAttributes());
    }

    public function testIssueParsesDatesAndPublicUrl(): void
    {
        $issue = CertificateIssue::fromArray(Fixtures::json('issue'));

        $this->assertTrue($issue->isActive());
        $this->assertFalse($issue->isPending());
        $issuedAt = $issue->getIssuedAt();
        $this->assertNotNull($issuedAt);
        $this->assertSame('2026-01-20T10:00:00+00:00', $issuedAt->format('c'));
        $this->assertSame('https://verifycate.com/c/7c1f0a5e-0f2b-4a1c-9f4d-2b3c4d5e6f70', $issue->getPublicUrl());
        $this->assertSame('40', $issue->getAttributes()['course_hours']);
    }

    public function testRecipientFiltersActiveIssues(): void
    {
        $recipient = Recipient::fromArray([
            'recipient_email' => 'ada@example.com',
            'recipient_name' => 'Ada Lovelace',
            'issue_count' => 2,
            'certificate_count' => 2,
            'issues' => [
                ['id' => '1', 'status' => 'active'],
                ['id' => '2', 'status' => 'pending'],
            ],
        ]);

        $this->assertCount(2, $recipient->getIssues());
        $this->assertCount(1, $recipient->getActiveIssues());
        $this->assertSame('1', $recipient->getActiveIssues()[0]->getId());
    }

    public function testImportResultHandlesAnEmptyBatch(): void
    {
        $result = ImportResult::fromArray([
            'items' => [],
            'rejected' => [],
            'summary' => ['total' => 0],
            'results' => [],
        ]);

        $this->assertNull($result->getFirstIssue());
        $this->assertFalse($result->hasRejections());
        $this->assertSame(['total' => 0], $result->getSummary());
    }

    public function testRecipientIssueBuildsAPublicUrl(): void
    {
        $issue = RecipientIssue::fromArray([
            'id' => '1',
            'public_id' => 'abc',
            'status' => 'active',
        ]);

        $this->assertSame('https://verifycate.com/c/abc', $issue->getPublicUrl());
        $this->assertTrue($issue->isActive());
    }
}
