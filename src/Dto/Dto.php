<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Dto;

abstract class Dto
{
    /** @var array<string, mixed> */
    protected array $raw;

    /**
     * @param array<string, mixed> $raw
     */
    public function __construct(array $raw)
    {
        $this->raw = $raw;
    }

    /**
     * The complete response payload, including fields the SDK does not model.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->raw;
    }

    /**
     * @return mixed
     */
    protected function value(string $key)
    {
        return $this->raw[$key] ?? null;
    }

    protected function string(string $key, string $default = ''): string
    {
        $value = $this->value($key);

        return is_scalar($value) ? (string) $value : $default;
    }

    protected function nullableString(string $key): ?string
    {
        $value = $this->value($key);

        return is_scalar($value) ? (string) $value : null;
    }

    protected function int(string $key, int $default = 0): int
    {
        $value = $this->value($key);

        return is_numeric($value) ? (int) $value : $default;
    }

    protected function nullableInt(string $key): ?int
    {
        $value = $this->value($key);

        return is_numeric($value) ? (int) $value : null;
    }

    protected function bool(string $key, bool $default = false): bool
    {
        $value = $this->value($key);

        if (is_bool($value)) {
            return $value;
        }

        if ($value === 1 || $value === '1' || $value === 'true') {
            return true;
        }

        if ($value === 0 || $value === '0' || $value === 'false') {
            return false;
        }

        return $default;
    }

    protected function dateTime(string $key): ?\DateTimeImmutable
    {
        $value = $this->value($key);
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function object(string $key): array
    {
        $value = $this->value($key);

        return is_array($value) ? $value : [];
    }

    /**
     * @return array<int, mixed>
     */
    protected function list(string $key): array
    {
        $value = $this->value($key);

        return is_array($value) ? array_values($value) : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function objectList(string $key): array
    {
        $result = [];
        foreach ($this->list($key) as $item) {
            if (is_array($item)) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @return array<int, string>
     */
    protected function stringList(string $key): array
    {
        $result = [];
        foreach ($this->list($key) as $item) {
            if (is_scalar($item)) {
                $result[] = (string) $item;
            }
        }

        return $result;
    }
}
