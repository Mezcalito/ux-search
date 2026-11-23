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
use Doctrine\DBAL\Logging\Middleware;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Mezcalito\UxSearchBundle\Adapter\Doctrine\DoctrineAdapter;
use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\SearchInterface;
use Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Bar;
use Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo;
use Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\FooSearch;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractDoctrineTestCase extends TestCase
{
    protected EntityManagerInterface $entityManager;

    protected SearchInterface $search;

    protected Query $query;

    protected DoctrineAdapter $adapter;

    /** @var list<string> */
    protected array $executedSql = [];

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

        $connectionParams = ['driver' => 'pdo_sqlite', 'memory' => true];

        $config = ORMSetup::createAttributeMetadataConfiguration($paths, true);
        if (\PHP_VERSION_ID >= 80400 && method_exists($config, 'enableNativeLazyObjects')) {
            $config->enableNativeLazyObjects(true);
        }

        $logger = new class($this->executedSql) extends AbstractLogger {
            /** @param list<string> $executedSql */
            public function __construct(private array &$executedSql)
            {
            }

            public function log($level, string|\Stringable $message, array $context = []): void
            {
                if (isset($context['sql']) && \is_string($context['sql'])) {
                    $this->executedSql[] = $context['sql'];
                }
            }
        };
        $config->setMiddlewares([new Middleware($logger)]);

        $connection = DriverManager::getConnection($connectionParams, $config);

        return new EntityManager($connection, $config);
    }

    protected function createDatabase(array $data = []): void
    {
        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->createSchema([
            $this->entityManager->getClassMetadata(Foo::class),
            $this->entityManager->getClassMetadata(Bar::class),
        ]);

        foreach ($data as $foo) {
            $this->entityManager->persist($foo);
        }

        $this->entityManager->flush();
    }
}
