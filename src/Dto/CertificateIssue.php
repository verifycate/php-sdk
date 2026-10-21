<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Dto;

final class CertificateIssue extends Dto
{
    public const FIELDS = [
        'id',
        'certificate_id',
        'public_id',
        'recipient_email',
        'recipient_name',
        'status',
        'anchor_status',
        'pending_reason',
        'attributes',
        'design_version',
        'issued_at',
        'expires_at',
        'notified_at',
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

    /**
     * pending, active or expired.
     */
    public function getStatus(): string
    {
        return $this->string('status');
    }

    /**
     * none, pending, partial, complete or failed.
     */
    public function getAnchorStatus(): string
    {
        return $this->string('anchor_status', 'none');
    }

    public function getPendingReason(): ?string
    {
        return $this->nullableString('pending_reason');
    }

    /**
     * @return array<string, mixed>
     */
    public function getAttributes(): array
    {
        return $this->object('attributes');
    }

    public function getDesignVersion(): ?int
    {
        return $this->nullableInt('design_version');
    }

    public function getIssuedAt(): ?\DateTimeImmutable
    {
        return $this->dateTime('issued_at');
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->dateTime('expires_at');
    }

    public function getNotifiedAt(): ?\DateTimeImmutable
    {
        return $this->dateTime('notified_at');
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->dateTime('created_at');
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->dateTime('updated_at');
    }

    public function isPending(): bool
    {
        return $this->getStatus() === 'pending';
    }

    public function isActive(): bool
    {
        return $this->getStatus() === 'active';
    }

    public function isExpired(): bool
    {
        return $this->getStatus() === 'expired';
    }

    /**
     * Public verification URL for the issued certificate.
     */
    public function getPublicUrl(string $portalBaseUrl = 'https://verifycate.com'): string
    {
        return rtrim($portalBaseUrl, '/') . '/c/' . rawurlencode($this->getPublicId());
    }
}
