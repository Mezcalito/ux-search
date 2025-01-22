<?php

/*
 * This file is part of the UxSearch project.
 *
 * (c) Mezcalito (https://www.mezcalito.fr)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Mezcalito\UxSearchBundle\Tests\Adapter;

use Mezcalito\UxSearchBundle\Adapter\AdapterFactoryInterface;
use Mezcalito\UxSearchBundle\Adapter\AdapterInterface;
use Mezcalito\UxSearchBundle\Adapter\AdapterProvider;
use Mezcalito\UxSearchBundle\Exception\AdapterException;
use PHPUnit\Framework\TestCase;

class AdapterProviderTest extends TestCase
{
    public function testGetAdapterReturnsDefaultAdapter(): void
    {
        $defaultAdapterName = 'default';
        $adapterConfiguration = [
            'default' => ['dsn' => 'meilisearch://localhost:7700'],
        ];
        $adapter = $this->createMock(AdapterInterface::class);

        $factory = $this->createMock(AdapterFactoryInterface::class);
        $factory->method('support')->willReturn(true);
        $factory->method('createAdapter')->willReturn($adapter);

        $provider = new AdapterProvider($defaultAdapterName, $adapterConfiguration, [$factory]);

        $result = $provider->getAdapter();

        $this->assertSame($adapter, $result);
    }

    public function testGetAdapterReturnsSpecifiedAdapter(): void
    {
        $adapterConfiguration = [
            'custom' => ['dsn' => 'meilisearch://custom:7700'],
        ];
        $adapter = $this->createMock(AdapterInterface::class);

        $factory = $this->createMock(AdapterFactoryInterface::class);
        $factory->method('support')->with('meilisearch://custom:7700')->willReturn(true);
        $factory->method('createAdapter')->with('meilisearch://custom:7700')->willReturn($adapter);

        $provider = new AdapterProvider('default', $adapterConfiguration, [$factory]);

        $result = $provider->getAdapter('custom');

        $this->assertSame($adapter, $result);
    }

    public function testGetAdapterThrowsExceptionIfConfigurationNotFound(): void
    {
        $provider = new AdapterProvider('default', [], []);

        $this->expectException(AdapterException::class);

        $provider->getAdapter('nonexistent');
    }

    public function testGetAdapterThrowsExceptionIfNoFactorySupportsDsn(): void
    {
        $adapterConfiguration = [
            'default' => ['dsn' => 'unsupported://localhost'],
        ];

        $factory = $this->createMock(AdapterFactoryInterface::class);
        $factory->method('support')->willReturn(false);

        $provider = new AdapterProvider('default', $adapterConfiguration, [$factory]);

        $this->expectException(AdapterException::class);

        $provider->getAdapter();
    }
}
