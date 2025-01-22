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

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Mezcalito\UxSearchBundle\Adapter\Doctrine\DoctrineAdapter;
use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\SearchInterface;
use Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo;
use Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\FooSearch;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractDoctrineTestCase extends TestCase
{
    protected EntityManagerInterface $entityManager;

    protected SearchInterface $search;

    protected Query $query;

    protected DoctrineAdapter $adapter;

    protected function setUp(): void
    {
        $this->entityManager = $this->getEntityManager();
        $this->search = new FooSearch();
        $this->search->build();

        $this->adapter = new DoctrineAdapter($this->entityManager);
        $this->query = $this->search->createQuery();

        $optionResolver = new OptionsResolver();
        $this->adapter->configureParameters($optionResolver);

        $this->search->setResolvedAdapterParameters($optionResolver->resolve($this->search->getAdapterParameters()));
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        $paths = [__DIR__.'/../../Fixture/Doctrine'];

        $dsnParser = new DsnParser();
        $connectionParams = $dsnParser->parse('pdo-sqlite:///:memory:');

        $config = ORMSetup::createAttributeMetadataConfiguration($paths, true);
        $connection = DriverManager::getConnection($connectionParams, $config);

        return new EntityManager($connection, $config);
    }

    protected function createDatabase(array $data = []): void
    {
        $schemaTool = new SchemaTool($this->entityManager);
        $class = $this->entityManager->getClassMetadata(Foo::class);
        $schemaTool->createSchema([$class]);

        foreach ($data as $foo) {
            $this->entityManager->persist($foo);
        }

        $this->entityManager->flush();
    }
}
