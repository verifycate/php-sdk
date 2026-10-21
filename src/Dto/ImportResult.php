<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Dto;

final class ImportResult extends Dto
{
    public const FIELDS = ['items', 'rejected', 'summary', 'results'];

    /**
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        return new self($raw);
    }

    /**
     * @return array<int, CertificateIssue>
     */
    public function getItems(): array
    {
        $items = [];
        foreach ($this->objectList('items') as $item) {
            $items[] = CertificateIssue::fromArray($item);
        }

        return $items;
    }

    /**
     * Emails skipped because the recipient already holds the certificate.
     *
     * @return array<int, string>
     */
    public function getRejected(): array
    {
        return $this->stringList('rejected');
    }

    /**
     * @return array<string, mixed>
     */
    public function getSummary(): array
    {
        return $this->object('summary');
    }

    /**
     * Per-row validation outcomes, in submission order.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getResults(): array
    {
        return $this->objectList('results');
    }

    public function getFirstIssue(): ?CertificateIssue
    {
        $items = $this->getItems();

        return $items === [] ? null : $items[0];
    }

    public function hasRejections(): bool
    {
        return $this->getRejected() !== [];
    }
}
