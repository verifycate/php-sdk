<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Dto;

final class VerificationResult extends Dto
{
    public const FIELDS = ['valid', 'checked_at', 'reason', 'blockchain', 'nft'];

    /**
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        return new self($raw);
    }

    public function isValid(): bool
    {
        return $this->bool('valid');
    }

    public function getCheckedAt(): ?\DateTimeImmutable
    {
        return $this->dateTime('checked_at');
    }

    /**
     * expired, unsigned, key_unavailable or invalid_signature.
     */
    public function getReason(): ?string
    {
        return $this->nullableString('reason');
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
}
