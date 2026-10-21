<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Dto;

final class Certificate extends Dto
{
    /**
     * Fields the SDK models. A test asserts these still exist in the OpenAPI schema.
     *
     * @var array<int, string>
     */
    public const FIELDS = [
        'id',
        'name',
        'status',
        'description',
        'certificate_type',
        'level',
        'duration',
        'learn_more_url',
        'validity_days',
        'blockchain_enabled',
        'has_design',
        'published_design_version',
        'pending_recipient_count',
        'attributes',
        'skills',
        'sponsors',
        'earning_criteria',
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

    public function getName(): string
    {
        return $this->string('name');
    }

    /**
     * draft, active or archived.
     */
    public function getStatus(): string
    {
        return $this->string('status');
    }

    public function getDescription(): string
    {
        return $this->string('description');
    }

    public function getCertificateType(): ?string
    {
        return $this->nullableString('certificate_type');
    }

    public function getLevel(): ?string
    {
        return $this->nullableString('level');
    }

    public function getDuration(): ?string
    {
        return $this->nullableString('duration');
    }

    public function getLearnMoreUrl(): ?string
    {
        return $this->nullableString('learn_more_url');
    }

    public function getValidityDays(): ?int
    {
        return $this->nullableInt('validity_days');
    }

    public function isBlockchainEnabled(): bool
    {
        return $this->bool('blockchain_enabled');
    }

    public function hasDesign(): bool
    {
        return $this->bool('has_design');
    }

    public function getPublishedDesignVersion(): ?int
    {
        return $this->nullableInt('published_design_version');
    }

    public function getPendingRecipientCount(): int
    {
        return $this->int('pending_recipient_count');
    }

    /**
     * Attribute definitions declared on the certificate.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAttributes(): array
    {
        return $this->objectList('attributes');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getSkills(): array
    {
        return $this->objectList('skills');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getSponsors(): array
    {
        return $this->objectList('sponsors');
    }

    /**
     * @return array<int, string>
     */
    public function getEarningCriteria(): array
    {
        return $this->stringList('earning_criteria');
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->dateTime('created_at');
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->dateTime('updated_at');
    }

    public function isDraft(): bool
    {
        return $this->getStatus() === 'draft';
    }

    public function isActive(): bool
    {
        return $this->getStatus() === 'active';
    }
}
