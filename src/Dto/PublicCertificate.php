<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Dto;

final class PublicCertificate extends Dto
{
    public const FIELDS = [
        'public_id',
        'status',
        'recipient_name',
        'certificate',
        'design_version',
        'issued_at',
        'expires_at',
        'png_url',
        'pdf_url',
        'attributes',
        'design',
        'blockchain',
        'nft',
    ];

    /**
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        return new self($raw);
    }

    public function getPublicId(): string
    {
        return $this->string('public_id');
    }

    /**
     * active or expired.
     */
    public function getStatus(): string
    {
        return $this->string('status');
    }

    public function getRecipientName(): string
    {
        return $this->string('recipient_name');
    }

    /**
     * @return array<string, mixed>
     */
    public function getCertificate(): array
    {
        return $this->object('certificate');
    }

    public function getCertificateName(): string
    {
        $certificate = $this->getCertificate();

        return isset($certificate['name']) && is_scalar($certificate['name'])
            ? (string) $certificate['name']
            : '';
    }

    public function getCertificateType(): ?string
    {
        $certificate = $this->getCertificate();

        return isset($certificate['certificate_type']) && is_scalar($certificate['certificate_type'])
            ? (string) $certificate['certificate_type']
            : null;
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

    public function getPngUrl(): string
    {
        return $this->string('png_url');
    }

    public function getPdfUrl(): string
    {
        return $this->string('pdf_url');
    }

    /**
     * @return array<string, mixed>
     */
    public function getAttributes(): array
    {
        return $this->object('attributes');
    }

    /**
     * @return array<string, mixed>
     */
    public function getDesign(): array
    {
        return $this->object('design');
    }

    /**
     * @return array<string, mixed>
     */
    public function getBlockchain(): array
    {
        return $this->object('blockchain');
    }

    /**
     * @return array<string, mixed>
     */
    public function getNft(): array
    {
        return $this->object('nft');
    }

    public function isActive(): bool
    {
        return $this->getStatus() === 'active';
    }

    public function getVerificationUrl(string $portalBaseUrl = 'https://verifycate.com'): string
    {
        return rtrim($portalBaseUrl, '/') . '/c/' . rawurlencode($this->getPublicId());
    }
}
