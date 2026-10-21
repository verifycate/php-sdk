<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Exception;

/**
 * An error response from the Verifycate API.
 *
 * The API uses RFC 7807 problem details for both 4xx and 5xx responses, so the
 * structured fields below are populated whenever the response is well formed.
 */
class ApiException extends \RuntimeException
{
    private int $statusCode;

    private string $errorCode;

    private string $title;

    private string $detail;

    private string $type;

    /** @var array<int, array<string, mixed>> */
    private array $errors;

    /** @var array<string, mixed> */
    private array $data;

    /** @var array<string, mixed> */
    private array $problem;

    /** @var array<string, array<int, string>> */
    private array $headers;

    /**
     * @param array<string, mixed> $problem
     * @param array<string, array<int, string>> $headers
     */
    public function __construct(
        int $statusCode,
        string $message,
        array $problem = [],
        array $headers = []
    ) {
        parent::__construct($message, $statusCode);

        $errors = $problem['errors'] ?? [];
        $data = $problem['data'] ?? [];

        $this->statusCode = $statusCode;
        $this->errorCode = isset($problem['code']) && is_scalar($problem['code'])
            ? (string) $problem['code']
            : '';
        $this->title = isset($problem['title']) && is_scalar($problem['title'])
            ? (string) $problem['title']
            : sprintf('HTTP %d', $statusCode);
        $this->detail = isset($problem['detail']) && is_scalar($problem['detail'])
            ? (string) $problem['detail']
            : '';
        $this->type = isset($problem['type']) && is_scalar($problem['type'])
            ? (string) $problem['type']
            : '';
        $this->errors = is_array($errors) ? array_values(array_filter($errors, 'is_array')) : [];
        $this->data = is_array($data) ? $data : [];
        $this->problem = $problem;
        $this->headers = $headers;
    }

    /**
     * @param array<string, mixed>|null $problem
     * @param array<string, array<int, string>> $headers
     */
    public static function fromResponse(int $statusCode, ?array $problem, array $headers = []): self
    {
        $problem = $problem ?? [];
        $title = isset($problem['title']) && is_scalar($problem['title'])
            ? (string) $problem['title']
            : sprintf('HTTP %d', $statusCode);
        $detail = isset($problem['detail']) && is_scalar($problem['detail'])
            ? (string) $problem['detail']
            : '';
        $code = isset($problem['code']) && is_scalar($problem['code'])
            ? (string) $problem['code']
            : '';

        $message = $detail !== '' ? $detail : $title;
        if ($code !== '' && $detail === '') {
            $message = $code;
        }

        return new self($statusCode, $message, $problem, $headers);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDetail(): string
    {
        return $this->detail;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return array<string, mixed>
     */
    public function getProblem(): array
    {
        return $this->problem;
    }

    public function getRetryAfter(): ?int
    {
        $value = $this->headers['retry-after'][0] ?? null;
        if ($value === null || !is_numeric($value)) {
            return null;
        }

        return max(0, (int) $value);
    }

    public function isUnauthorized(): bool
    {
        return $this->statusCode === 401;
    }

    public function isPaymentRequired(): bool
    {
        return $this->statusCode === 402;
    }

    public function isForbidden(): bool
    {
        return $this->statusCode === 403;
    }

    public function isNotFound(): bool
    {
        return $this->statusCode === 404;
    }

    public function isConflict(): bool
    {
        return $this->statusCode === 409;
    }

    public function isValidationError(): bool
    {
        return $this->statusCode === 422;
    }

    public function isRateLimited(): bool
    {
        return $this->statusCode === 429;
    }

    /**
     * Server errors, rate limits and connection failures are worth retrying.
     * Other 4xx responses are permanent until the request changes.
     */
    public function isRetryable(): bool
    {
        return $this->statusCode === 429 || $this->statusCode >= 500;
    }
}
