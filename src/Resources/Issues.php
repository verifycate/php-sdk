<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Resources;

use Verifycate\Sdk\Dto\CertificateIssue;
use Verifycate\Sdk\Dto\CertificateIssuePage;
use Verifycate\Sdk\Dto\ImportResult;
use Verifycate\Sdk\Operations;

final class Issues extends Resource
{
    private const LIST_FILTERS = [
        'search',
        'status',
        'engagement_status',
        'design_version',
    ];

    /**
     * @param array<string, mixed> $filters search, status, engagement_status, design_version.
     */
    public function listForCertificate(
        string $certificateId,
        array $filters = [],
        int $limit = 50,
        int $offset = 0
    ): CertificateIssuePage {
        if (trim($certificateId) === '') {
            throw new \InvalidArgumentException('A certificate id is required.');
        }

        if ($limit < 1 || $limit > 200) {
            throw new \InvalidArgumentException('The page limit must be between 1 and 200.');
        }

        if ($offset < 0) {
            throw new \InvalidArgumentException('The page offset cannot be negative.');
        }

        $query = ['limit' => $limit, 'offset' => $offset];
        foreach ($filters as $key => $value) {
            if (!in_array($key, self::LIST_FILTERS, true)) {
                throw new \InvalidArgumentException(sprintf('Unsupported issue filter "%s".', (string) $key));
            }

            $query[$key] = $value;
        }

        return CertificateIssuePage::fromArray(
            $this->client->request(
                Operations::LIST_ISSUES,
                ['slug' => $this->workspace(), 'id' => $certificateId],
                $query
            )
        );
    }

    /**
     * Create pending issues for one or more recipients.
     *
     * Each recipient is an IssueInput array: email (required), name, attributes
     * and expires_at. The API rejects recipients that already hold the
     * certificate and returns them in ImportResult::getRejected().
     *
     * @param array<int, array<string, mixed>> $recipients
     * @param string|null $source manual or import; API-key callers are recorded as api when omitted.
     */
    public function import(string $certificateId, array $recipients, ?string $source = null): ImportResult
    {
        if (trim($certificateId) === '') {
            throw new \InvalidArgumentException('A certificate id is required.');
        }

        if ($recipients === []) {
            throw new \InvalidArgumentException('At least one recipient is required.');
        }

        foreach ($recipients as $index => $recipient) {
            if (!is_array($recipient)) {
                throw new \InvalidArgumentException(sprintf('Recipient %d must be an array.', (int) $index));
            }

            if (!isset($recipient['email']) || !is_string($recipient['email']) || trim($recipient['email']) === '') {
                throw new \InvalidArgumentException(sprintf('Recipient %d needs an email address.', (int) $index));
            }
        }

        $body = ['issues' => array_values($recipients)];

        if ($source !== null) {
            if (!in_array($source, ['manual', 'import'], true)) {
                throw new \InvalidArgumentException('The import source must be "manual" or "import".');
            }

            $body['source'] = $source;
        }

        return ImportResult::fromArray(
            $this->client->request(
                Operations::IMPORT_ISSUES,
                ['slug' => $this->workspace(), 'id' => $certificateId],
                [],
                $body
            )
        );
    }

    public function get(string $issueId): CertificateIssue
    {
        $this->assertId($issueId);

        return CertificateIssue::fromArray(
            $this->client->request(
                Operations::GET_ISSUE,
                ['slug' => $this->workspace(), 'issueID' => $issueId]
            )
        );
    }

    /**
     * @param array<string, mixed> $payload UpdateIssueBody fields: name, email, attributes, expires_at.
     */
    public function update(string $issueId, array $payload): CertificateIssue
    {
        $this->assertId($issueId);

        if ($payload === []) {
            throw new \InvalidArgumentException('Updating an issue needs at least one field.');
        }

        return CertificateIssue::fromArray(
            $this->client->request(
                Operations::UPDATE_ISSUE,
                ['slug' => $this->workspace(), 'issueID' => $issueId],
                [],
                $payload
            )
        );
    }

    public function delete(string $issueId): bool
    {
        $this->assertId($issueId);

        $payload = $this->client->request(
            Operations::DELETE_ISSUE,
            ['slug' => $this->workspace(), 'issueID' => $issueId]
        );

        return ($payload['ok'] ?? true) === true;
    }

    public function issue(string $issueId): CertificateIssue
    {
        $this->assertId($issueId);

        return CertificateIssue::fromArray(
            $this->client->request(
                Operations::ISSUE_CERTIFICATE,
                ['slug' => $this->workspace(), 'issueID' => $issueId]
            )
        );
    }

    public function reissue(string $issueId): CertificateIssue
    {
        $this->assertId($issueId);

        return CertificateIssue::fromArray(
            $this->client->request(
                Operations::REISSUE_CERTIFICATE,
                ['slug' => $this->workspace(), 'issueID' => $issueId]
            )
        );
    }

    /**
     * Resend the recipient's unlock form.
     */
    public function remind(string $issueId): bool
    {
        $this->assertId($issueId);

        $payload = $this->client->request(
            Operations::REMIND_ISSUE,
            ['slug' => $this->workspace(), 'issueID' => $issueId]
        );

        return ($payload['ok'] ?? true) === true;
    }

    private function assertId(string $issueId): void
    {
        if (trim($issueId) === '') {
            throw new \InvalidArgumentException('An issue id is required.');
        }
    }
}
