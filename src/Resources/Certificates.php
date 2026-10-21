<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Resources;

use Verifycate\Sdk\Dto\Certificate;
use Verifycate\Sdk\Dto\CertificatePage;
use Verifycate\Sdk\Operations;

final class Certificates extends Resource
{
    public function list(?string $search = null, int $limit = 50, int $offset = 0): CertificatePage
    {
        $this->assertPage($limit, $offset);

        $payload = $this->client->request(
            Operations::LIST_CERTIFICATES,
            ['slug' => $this->workspace()],
            ['search' => $search, 'limit' => $limit, 'offset' => $offset]
        );

        return CertificatePage::fromArray($payload);
    }

    public function get(string $id): Certificate
    {
        $this->assertId($id, 'certificate');

        return Certificate::fromArray(
            $this->client->request(
                Operations::GET_CERTIFICATE,
                ['slug' => $this->workspace(), 'id' => $id]
            )
        );
    }

    /**
     * @param array<string, mixed> $payload CertificateCreateBody fields.
     */
    public function create(array $payload): Certificate
    {
        if (!isset($payload['name']) || !is_string($payload['name']) || trim($payload['name']) === '') {
            throw new \InvalidArgumentException('Creating a certificate needs a non-empty name.');
        }

        return Certificate::fromArray(
            $this->client->request(
                Operations::CREATE_CERTIFICATE,
                ['slug' => $this->workspace()],
                [],
                $payload
            )
        );
    }

    /**
     * @param array<string, mixed> $payload CertificateUpdateBody fields.
     */
    public function update(string $id, array $payload): Certificate
    {
        $this->assertId($id, 'certificate');

        if ($payload === []) {
            throw new \InvalidArgumentException('Updating a certificate needs at least one field.');
        }

        return Certificate::fromArray(
            $this->client->request(
                Operations::UPDATE_CERTIFICATE,
                ['slug' => $this->workspace(), 'id' => $id],
                [],
                $payload
            )
        );
    }

    public function delete(string $id): bool
    {
        $this->assertId($id, 'certificate');

        $payload = $this->client->request(
            Operations::DELETE_CERTIFICATE,
            ['slug' => $this->workspace(), 'id' => $id]
        );

        return ($payload['ok'] ?? true) === true;
    }

    private function assertId(string $id, string $resource): void
    {
        if (trim($id) === '') {
            throw new \InvalidArgumentException(sprintf('A %s id is required.', $resource));
        }
    }

    private function assertPage(int $limit, int $offset): void
    {
        if ($limit < 1 || $limit > 200) {
            throw new \InvalidArgumentException('The page limit must be between 1 and 200.');
        }

        if ($offset < 0) {
            throw new \InvalidArgumentException('The page offset cannot be negative.');
        }
    }
}
