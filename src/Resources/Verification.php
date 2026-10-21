<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Resources;

use Verifycate\Sdk\Dto\PublicCertificate;
use Verifycate\Sdk\Dto\VerificationResult;
use Verifycate\Sdk\Operations;

final class Verification extends Resource
{
    /**
     * The workspace query parameter is only needed when a public id is
     * ambiguous. The client falls back to the configured workspace.
     */
    public function get(string $publicId, ?string $workspace = null): PublicCertificate
    {
        $this->assertPublicId($publicId);

        return PublicCertificate::fromArray(
            $this->client->request(
                Operations::GET_PUBLIC_CERTIFICATE,
                ['publicID' => $publicId],
                ['workspace' => $this->workspaceOrNull($workspace)]
            )
        );
    }

    public function verify(string $publicId, ?string $workspace = null): VerificationResult
    {
        $this->assertPublicId($publicId);

        return VerificationResult::fromArray(
            $this->client->request(
                Operations::VERIFY_PUBLIC_CERTIFICATE,
                ['publicID' => $publicId],
                ['workspace' => $this->workspaceOrNull($workspace)]
            )
        );
    }

    private function workspaceOrNull(?string $workspace): ?string
    {
        if ($workspace !== null && trim($workspace) !== '') {
            return trim($workspace);
        }

        return $this->client->getConfig()->getWorkspace();
    }

    private function assertPublicId(string $publicId): void
    {
        if (trim($publicId) === '') {
            throw new \InvalidArgumentException('A certificate public id is required.');
        }
    }
}
