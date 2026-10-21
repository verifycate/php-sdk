<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Dto;

final class WorkspaceInfo extends Dto
{
    public const FIELDS = [
        'slug',
        'name',
        'issuer_name',
        'seat_live',
        'permissions',
        'full_access',
    ];

    /**
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        return new self($raw);
    }

    public function getSlug(): string
    {
        return $this->string('slug');
    }

    public function getName(): string
    {
        return $this->string('name');
    }

    public function getIssuerName(): string
    {
        return $this->string('issuer_name');
    }

    public function isSeatLive(): bool
    {
        return $this->bool('seat_live');
    }

    /**
     * @return array<int, string>
     */
    public function getPermissions(): array
    {
        return $this->stringList('permissions');
    }

    public function hasFullAccess(): bool
    {
        return $this->bool('full_access');
    }

    public function canReadCertificates(): bool
    {
        return $this->hasPermission('certificates:read');
    }

    public function canWriteCertificates(): bool
    {
        return $this->hasPermission('certificates:write');
    }

    private function hasPermission(string $permission): bool
    {
        return $this->hasFullAccess() || in_array($permission, $this->getPermissions(), true);
    }
}
