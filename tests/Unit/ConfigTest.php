<?php

declare(strict_types=1);

namespace Verifycate\Sdk\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Verifycate\Sdk\Config;
use Verifycate\Sdk\Exception\ConfigurationException;

final class ConfigTest extends TestCase
{
    public function testNormalisesTheBaseUrl(): void
    {
        $config = new Config('vc_key', ' acme ', 'https://api.example.test///');

        $this->assertSame('acme', $config->getWorkspace());
        $this->assertSame('https://api.example.test', $config->getBaseUrl());
        $this->assertTrue($config->hasApiKey());
    }

    public function testRejectsAnInvalidBaseUrl(): void
    {
        $this->expectException(ConfigurationException::class);

        new Config('vc_key', 'acme', 'api.example.test');
    }

    public function testRejectsANonPositiveTimeout(): void
    {
        $this->expectException(ConfigurationException::class);

        new Config('vc_key', 'acme', 'https://api.example.test', 0.0);
    }

    public function testRequiresAWorkspaceOnlyWhenAnOperationNeedsOne(): void
    {
        $config = new Config('vc_key');

        $this->assertNull($config->getWorkspace());

        $this->expectException(ConfigurationException::class);
        $config->requireWorkspace();
    }

    public function testAllowsAnonymousConfigurationForPublicVerification(): void
    {
        $config = new Config('', 'acme');

        $this->assertFalse($config->hasApiKey());
        $this->assertSame('acme', $config->requireWorkspace());
    }

    public function testUsesTheSdkUserAgentByDefault(): void
    {
        $config = new Config('vc_key');

        $this->assertStringStartsWith('verifycate-php-sdk/', $config->getUserAgent());
    }
}
