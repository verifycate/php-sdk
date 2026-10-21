<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Dto;

final class CertificateIssuePage extends Dto
{
    public const FIELDS = ['items', 'total', 'limit', 'offset'];

    /** @var array<int, CertificateIssue> */
    private array $items;

    private int $total;

    private int $limit;

    private int $offset;

    /**
     * @param array<int, CertificateIssue> $items
     * @param array<string, mixed> $raw
     */
    public function __construct(array $items, int $total, int $limit, int $offset, array $raw = [])
    {
        parent::__construct($raw);

        $this->items = $items;
        $this->total = $total;
        $this->limit = $limit;
        $this->offset = $offset;
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        $items = [];
        foreach (isset($raw['items']) && is_array($raw['items']) ? $raw['items'] : [] as $item) {
            if (is_array($item)) {
                $items[] = CertificateIssue::fromArray($item);
            }
        }

        return new self(
            $items,
            isset($raw['total']) && is_numeric($raw['total']) ? (int) $raw['total'] : count($items),
            isset($raw['limit']) && is_numeric($raw['limit']) ? (int) $raw['limit'] : count($items),
            isset($raw['offset']) && is_numeric($raw['offset']) ? (int) $raw['offset'] : 0,
            $raw
        );
    }

    /**
     * @return array<int, CertificateIssue>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }
}
