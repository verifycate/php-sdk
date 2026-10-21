<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Dto;

final class RecipientIssue extends Dto
{
    public const FIELDS = [
        'id',
        'certificate_id',
        'certificate_name',
        'certificate_status',
        'public_id',
        'recipient_email',
        'recipient_name',
        'status',
        'pending_reason',
        'issued_at',
        'expires_at',
        'created_at',
        'updated_at',
    ];

    /**
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        return new self($raw);
    }

    public function getId(): string
    {
        return $this->string('id');
    }

    public function getCertificateId(): string
    {
        return $this->string('certificate_id');
    }

    public function getCertificateName(): string
    {
        return $this->string('certificate_name');
    }

    public function getCertificateStatus(): string
    {
        return $this->string('certificate_status');
    }

    public function getPublicId(): string
    {
        return $this->string('public_id');
    }

    public function getRecipientEmail(): string
    {
        return $this->string('recipient_email');
    }

    public function getRecipientName(): string
    {
        return $this->string('recipient_name');
    }

    public function getStatus(): string
    {
        return $this->string('status');
    }

    public function getPendingReason(): ?string
    {
        return $this->nullableString('pending_reason');
    }

    public function getIssuedAt(): ?\DateTimeImmutable
    {
        return $this->dateTime('issued_at');
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->dateTime('expires_at');
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->dateTime('created_at');
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->dateTime('updated_at');
    }

    public function isActive(): bool
    {
        return $this->getStatus() === 'active';
    }

    public function isPending(): bool
    {
        return $this->getStatus() === 'pending';
    }

    public function getPublicUrl(string $portalBaseUrl = 'https://verifycate.com'): string
    {
        return rtrim($portalBaseUrl, '/') . '/c/' . rawurlencode($this->getPublicId());
    }
}
