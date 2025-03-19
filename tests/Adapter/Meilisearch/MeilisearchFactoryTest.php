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

namespace Mezcalito\UxSearchBundle\Tests\Adapter\Meilisearch;

use Meilisearch\Client;
use Mezcalito\UxSearchBundle\Adapter\Meilisearch\MeilisearchAdapter;
use Mezcalito\UxSearchBundle\Adapter\Meilisearch\MeilisearchFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;

class MeilisearchFactoryTest extends TestCase
{
    public function testSupportReturnsTrueForMeilisearchDsn(): void
    {
        $factory = new MeilisearchFactory($this->createMock(ClientInterface::class));

        $this->assertTrue($factory->support('meilisearch://localhost:7700'));
        $this->assertFalse($factory->support('algolia://localhost:7700'));
    }

    public function testCreateAdapterReturnsMeilisearchAdapter(): void
    {
        $factory = $this->getMockBuilder(MeilisearchFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createClient'])
            ->getMock();

        $client = $this->createMock(Client::class);
        $factory->expects($this->once())
            ->method('createClient')
            ->with('meilisearch://localhost:7700')
            ->willReturn($client);

        $adapter = $factory->createAdapter('meilisearch://localhost:7700');

        $this->assertInstanceOf(MeilisearchAdapter::class, $adapter);
    }

    public function testCreateClientParsesDsnCorrectly(): void
    {
        if (!class_exists(Client::class)) {
            $this->markTestSkipped('Meilisearch Client is not installed.');
        }

        $factory = new MeilisearchFactory($this->createMock(ClientInterface::class));

        $dsn = 'meilisearch://user@localhost:7700?tls=true';
        $client = $factory->createClient($dsn);

        $this->assertInstanceOf(Client::class, $client);
    }

    public function testCreateClientWithOptionalHttpClient(): void
    {
        $factory = new MeilisearchFactory($this->createMock(ClientInterface::class));
        $client = $factory->createClient('meilisearch://localhost:7700');

        $this->assertInstanceOf(Client::class, $client);
    }
}
