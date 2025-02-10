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

namespace Mezcalito\UxSearchBundle\Tests\Adapter\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Mezcalito\UxSearchBundle\Adapter\Doctrine\DoctrineAdapter;
use Mezcalito\UxSearchBundle\Adapter\Doctrine\DoctrineFactory;
use Mezcalito\UxSearchBundle\Exception\DoctrineAdapterException;
use PHPUnit\Framework\TestCase;

class DoctrineFactoryTest extends TestCase
{
    public function testSupportReturnsTrueForValidDsn(): void
    {
        $factory = new DoctrineFactory();

        $this->assertTrue($factory->support('doctrine://default'));
    }

    public function testSupportReturnsFalseForInvalidDsn(): void
    {
        $factory = new DoctrineFactory();

        $this->assertFalse($factory->support('meilisearch://default'));
    }

    public function testCreateAdapterWithValidDsn(): void
    {
        $entityManagerMock = $this->createMock(EntityManager::class);
        $managerRegistryMock = $this->createMock(ManagerRegistry::class);
        $managerRegistryMock->expects($this->once())
            ->method('getManager')
            ->with('default')
            ->willReturn($entityManagerMock);

        $factory = new DoctrineFactory($managerRegistryMock);

        $adapter = $factory->createAdapter('doctrine://default');

        $this->assertInstanceOf(DoctrineAdapter::class, $adapter);
    }

    public function testCreateAdapterThrowsLogicExceptionWhenManagerRegistryIsNull(): void
    {
        $factory = new DoctrineFactory();

        $this->expectException(\LogicException::class);

        $factory->createAdapter('doctrine://default');
    }

    public function testCreateAdapterThrowsDoctrineAdapterExceptionForInvalidManager(): void
    {
        $invalidManager = $this->createMock(ObjectManager::class);
        $managerRegistryMock = $this->createMock(ManagerRegistry::class);
        $managerRegistryMock->expects($this->once())
            ->method('getManager')
            ->with('default')
            ->willReturn($invalidManager);

        $factory = new DoctrineFactory($managerRegistryMock);

        $this->expectException(DoctrineAdapterException::class);

        $factory->createAdapter('doctrine://default');
    }
}
