<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Verifycate\Sdk\Exception\ApiException;
use Verifycate\Sdk\Tests\Support\Fixtures;

final class ApiExceptionTest extends TestCase
{
    /**
     * @return array<string, array{int, bool, bool}>
     */
    public static function statusProvider(): array
    {
        return [
            'bad request' => [400, false, false],
            'unauthorized' => [401, false, true],
            'payment required' => [402, false, false],
            'forbidden' => [403, false, false],
            'not found' => [404, false, false],
            'unprocessable' => [422, false, false],
            'rate limited' => [429, true, false],
            'server error' => [500, true, false],
        ];
    }

    /**
     * @dataProvider statusProvider
     */
    public function testClassifiesErrorResponses(int $status, bool $retryable, bool $unauthorized): void
    {
        $exception = ApiException::fromResponse($status, Fixtures::problem($status));

        $this->assertSame($status, $exception->getStatusCode());
        $this->assertSame($status, $exception->getCode());
        $this->assertSame($retryable, $exception->isRetryable());
        $this->assertSame($unauthorized, $exception->isUnauthorized());
    }

    public function testExposesProblemFields(): void
    {
        $exception = ApiException::fromResponse(422, Fixtures::problem(422));

        $this->assertSame('recipient_validation_failed', $exception->getErrorCode());
        $this->assertSame('about:blank', $exception->getType());
        $this->assertCount(1, $exception->getErrors());
        $this->assertSame('The recipient could not be validated.', $exception->getMessage());
        $this->assertSame(Fixtures::problem(422), $exception->getProblem());
    }

    public function testFallsBackWhenTheProblemBodyIsMissing(): void
    {
        $exception = ApiException::fromResponse(503, null);

        $this->assertSame('HTTP 503', $exception->getTitle());
        $this->assertSame('HTTP 503', $exception->getMessage());
        $this->assertSame('', $exception->getErrorCode());
        $this->assertTrue($exception->isRetryable());
    }

    public function testParsesRetryAfter(): void
    {
        $exception = new ApiException(429, 'Slow down.', [], ['retry-after' => ['45']]);

        $this->assertSame(45, $exception->getRetryAfter());
    }
}
