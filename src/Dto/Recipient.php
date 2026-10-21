<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Dto;

final class Recipient extends Dto
{
    public const FIELDS = [
        'recipient_email',
        'recipient_name',
        'issue_count',
        'certificate_count',
        'latest_created_at',
        'latest_issued_at',
        'last_engaged_at',
        'issues',
    ];

    /**
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        return new self($raw);
    }

    public function getEmail(): string
    {
        return $this->string('recipient_email');
    }

    public function getName(): string
    {
        return $this->string('recipient_name');
    }

    public function getIssueCount(): int
    {
        return $this->int('issue_count');
    }

    public function getCertificateCount(): int
    {
        return $this->int('certificate_count');
    }

    public function getLatestCreatedAt(): ?\DateTimeImmutable
    {
        return $this->dateTime('latest_created_at');
    }

    public function getLatestIssuedAt(): ?\DateTimeImmutable
    {
        return $this->dateTime('latest_issued_at');
    }

    public function getLastEngagedAt(): ?\DateTimeImmutable
    {
        return $this->dateTime('last_engaged_at');
    }

    /**
     * @return array<int, RecipientIssue>
     */
    public function getIssues(): array
    {
        $issues = [];
        foreach ($this->objectList('issues') as $issue) {
            $issues[] = RecipientIssue::fromArray($issue);
        }

        return $issues;
    }

    /**
     * @return array<int, RecipientIssue>
     */
    public function getActiveIssues(): array
    {
        return array_values(array_filter(
            $this->getIssues(),
            static function (RecipientIssue $issue): bool {
                return $issue->isActive();
            }
        ));
    }
}
