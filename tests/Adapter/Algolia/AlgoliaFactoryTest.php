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

namespace Mezcalito\UxSearchBundle\Tests\Adapter\Algolia;

use Algolia\AlgoliaSearch\Api\SearchClient;
use Mezcalito\UxSearchBundle\Adapter\Algolia\AlgoliaAdapter;
use Mezcalito\UxSearchBundle\Adapter\Algolia\AlgoliaFactory;
use PHPUnit\Framework\TestCase;

class AlgoliaFactoryTest extends TestCase
{
    public function testSupportReturnsTrueForAlgoliaDsn(): void
    {
        $factory = new AlgoliaFactory();

        $this->assertTrue($factory->support('algolia://secret@index'));
        $this->assertFalse($factory->support('meilisearch://localhost:7700'));
    }

    public function testCreateAdapterReturnsAlgoliaAdapter(): void
    {
        $factory = $this->getMockBuilder(AlgoliaFactory::class)
            ->onlyMethods(['createClient'])
            ->getMock();

        $client = $this->createStub(SearchClient::class);
        $factory->expects($this->once())
            ->method('createClient')
            ->with('algolia://secret@index')
            ->willReturn($client);

        $adapter = $factory->createAdapter('algolia://secret@index');

        $this->assertInstanceOf(AlgoliaAdapter::class, $adapter);
    }

    public function testCreateClientParsesDsnCorrectly(): void
    {
        if (!class_exists(SearchClient::class)) {
            $this->markTestSkipped('Algolia Client is not installed.');
        }

        $factory = new AlgoliaFactory();

        $dsn = 'algolia://secret@index';
        $client = $factory->createClient($dsn);

        $this->assertInstanceOf(SearchClient::class, $client);
    }
}
