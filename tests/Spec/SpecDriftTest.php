<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Tests\Spec;

use PHPUnit\Framework\TestCase;
use Verifycate\Sdk\Dto\Certificate;
use Verifycate\Sdk\Dto\CertificateIssue;
use Verifycate\Sdk\Dto\CertificateIssuePage;
use Verifycate\Sdk\Dto\CertificatePage;
use Verifycate\Sdk\Dto\ImportResult;
use Verifycate\Sdk\Dto\PublicCertificate;
use Verifycate\Sdk\Dto\Recipient;
use Verifycate\Sdk\Dto\RecipientIssue;
use Verifycate\Sdk\Dto\RecipientPage;
use Verifycate\Sdk\Dto\VerificationResult;
use Verifycate\Sdk\Dto\WorkspaceInfo;
use Verifycate\Sdk\Operations;

final class SpecDriftTest extends TestCase
{
    /** @var array<string, mixed> */
    private static array $spec = [];

    public static function setUpBeforeClass(): void
    {
        $path = dirname(__DIR__, 2) . '/openapi/openapi.json';
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new \RuntimeException(sprintf('Missing OpenAPI snapshot at %s.', $path));
        }

        $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            throw new \RuntimeException('The OpenAPI snapshot is not a JSON object.');
        }

        /** @var array<string, mixed> $decoded */
        self::$spec = $decoded;
    }

    public function testTheSnapshotIsThePublicDocument(): void
    {
        $this->assertSame('Verifycate API', self::$spec['info']['title']);
        $this->assertSame('1.0.0', self::$spec['info']['version']);
        $this->assertArrayHasKey('apiKeyAuth', self::$spec['components']['securitySchemes']);
    }

    public function testEveryOperationStillExistsWithTheSameContract(): void
    {
        foreach (Operations::all() as $name => $operation) {
            $method = strtolower((string) $operation['method']);
            $path = (string) $operation['path'];
            $label = sprintf('%s (%s %s)', $name, strtoupper($method), $path);

            $this->assertArrayHasKey($path, self::$spec['paths'], $label . ' path is missing');
            $this->assertArrayHasKey($method, self::$spec['paths'][$path], $label . ' method is missing');

            /** @var array<string, mixed> $specOperation */
            $specOperation = self::$spec['paths'][$path][$method];

            if (!empty($operation['public'])) {
                $this->assertArrayNotHasKey(
                    'x-verifycate-api-key-policy',
                    $specOperation,
                    $label . ' is public but now has an API-key policy'
                );
            } else {
                $this->assertArrayHasKey('policy', $operation, $label . ' needs a policy or public flag');
                $this->assertSame(
                    $operation['policy'],
                    $specOperation['x-verifycate-api-key-policy'] ?? null,
                    $label . ' policy changed'
                );
            }

            $queryParameters = $this->parameterNames($specOperation, 'query');
            foreach ($operation['query'] ?? [] as $parameter) {
                $this->assertContains(
                    $parameter,
                    $queryParameters,
                    sprintf('%s no longer accepts the "%s" query parameter', $label, (string) $parameter)
                );
            }

            $responseRef = $this->responseSchemaRef($specOperation);
            $this->assertSame(
                '#/components/schemas/' . $operation['response'],
                $responseRef,
                $label . ' response schema changed'
            );

            if (isset($operation['body'])) {
                $bodyRef = $specOperation['requestBody']['content']['application/json']['schema']['$ref'] ?? null;
                $this->assertSame(
                    '#/components/schemas/' . $operation['body'],
                    $bodyRef,
                    $label . ' request schema changed'
                );

                $required = $this->schema((string) $operation['body'])['required'] ?? [];
                foreach ($operation['body_required'] ?? [] as $field) {
                    $this->assertContains(
                        $field,
                        $required,
                        sprintf('%s no longer requires the "%s" body field', $label, (string) $field)
                    );
                }
            }
        }
    }

    public function testOperationsAreUnique(): void
    {
        $seen = [];

        foreach (Operations::all() as $name => $operation) {
            $key = strtoupper((string) $operation['method']) . ' ' . $operation['path'];
            $this->assertArrayNotHasKey($key, $seen, sprintf('%s duplicates the %s operation.', $name, $key));
            $seen[$key] = true;
        }
    }

    public function testModeledDtoFieldsStillExistInTheSpec(): void
    {
        $map = [
            Certificate::class => ['CertificateSummary', 'CertificateDetail'],
            CertificatePage::class => ['CertificateListOutputBody'],
            CertificateIssue::class => ['CertificateIssue'],
            CertificateIssuePage::class => ['ListIssuesOutputBody'],
            ImportResult::class => ['ImportIssuesOutputBody'],
            Recipient::class => ['WorkspaceRecipientDetail'],
            RecipientIssue::class => ['WorkspaceRecipientIssue'],
            RecipientPage::class => ['ListWorkspaceRecipientsOutputBody'],
            PublicCertificate::class => ['PublicCertificateBody'],
            VerificationResult::class => ['VerifyCertificateOutputBody'],
            WorkspaceInfo::class => ['CurrentWorkspaceBody'],
        ];

        foreach ($map as $class => $schemaNames) {
            foreach ($class::FIELDS as $field) {
                $found = false;

                foreach ($schemaNames as $schemaName) {
                    $schema = $this->schema($schemaName);
                    if (
                        isset($schema['properties'])
                        && is_array($schema['properties'])
                        && array_key_exists($field, $schema['properties'])
                    ) {
                        $found = true;
                        break;
                    }
                }

                $this->assertTrue(
                    $found,
                    sprintf('%s::%s no longer exists in %s.', $class, $field, implode(' or ', $schemaNames))
                );
            }
        }
    }

    public function testEveryOperationTargetsTheVersionedAPI(): void
    {
        foreach (Operations::all() as $name => $operation) {
            $this->assertStringStartsWith(
                '/api/v1/',
                (string) $operation['path'],
                sprintf('%s does not target /api/v1.', $name)
            );
        }
    }

    /**
     * @param array<string, mixed> $operation
     * @return array<int, string>
     */
    private function parameterNames(array $operation, string $in): array
    {
        $names = [];

        foreach ($operation['parameters'] ?? [] as $parameter) {
            if (!is_array($parameter)) {
                continue;
            }

            if (($parameter['in'] ?? null) === $in && isset($parameter['name'])) {
                $names[] = (string) $parameter['name'];
            }
        }

        return $names;
    }

    /**
     * @param array<string, mixed> $operation
     */
    private function responseSchemaRef(array $operation): ?string
    {
        $ref = $operation['responses']['200']['content']['application/json']['schema']['$ref'] ?? null;

        return is_string($ref) ? $ref : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function schema(string $name): array
    {
        $schema = self::$spec['components']['schemas'][$name] ?? null;

        if (!is_array($schema)) {
            $this->fail(sprintf('Schema %s is missing from the OpenAPI document.', $name));
        }

        /** @var array<string, mixed> $schema */
        return $schema;
    }
}
