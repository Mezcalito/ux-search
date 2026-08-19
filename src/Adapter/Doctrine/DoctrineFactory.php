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

namespace Mezcalito\UxSearchBundle\Adapter\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Mezcalito\UxSearchBundle\Adapter\AdapterFactoryInterface;
use Mezcalito\UxSearchBundle\Adapter\AdapterInterface;
use Mezcalito\UxSearchBundle\Exception\DoctrineAdapterException;

readonly class DoctrineFactory implements AdapterFactoryInterface
{
    public function __construct(private ?ManagerRegistry $managerRegistry = null)
    {
    }

    public function support(string $dsn): bool
    {
        return str_starts_with($dsn, 'doctrine');
    }

    public function createAdapter(string $dsn): AdapterInterface
    {
        if (!$this->managerRegistry instanceof ManagerRegistry) {
            throw new \LogicException(\sprintf('You cannot use the "%s" as Doctrine ORM is not installed. Try running "composer require symfony/orm-pack".', self::class));
        }

        $parsedDsn = parse_url($dsn);
        $managerName = false !== $parsedDsn ? ($parsedDsn['host'] ?? 'default') : 'default';

        $manager = $this->managerRegistry->getManager($managerName);

        if (!$manager instanceof EntityManagerInterface) {
            throw DoctrineAdapterException::isNotOrmManager($managerName);
        }

        return new DoctrineAdapter($manager);
    }
}
