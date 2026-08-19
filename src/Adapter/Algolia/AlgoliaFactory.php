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

namespace Mezcalito\UxSearchBundle\Adapter\Algolia;

use Algolia\AlgoliaSearch\Api\SearchClient;
use Mezcalito\UxSearchBundle\Adapter\AdapterFactoryInterface;
use Mezcalito\UxSearchBundle\Adapter\AdapterInterface;
use Mezcalito\UxSearchBundle\Exception\AdapterException;

readonly class AlgoliaFactory implements AdapterFactoryInterface
{
    public function support(string $dsn): bool
    {
        return str_starts_with($dsn, 'algolia');
    }

    public function createAdapter(string $dsn): AdapterInterface
    {
        return new AlgoliaAdapter($this->createClient($dsn), new QueryBuilder());
    }

    public function createClient(string $dsn): SearchClient
    {
        if (!class_exists(SearchClient::class)) {
            throw new \LogicException(\sprintf('You cannot use the "%s" as Algolia, Client is not installed. Try running "algolia/algoliasearch-client-php".', self::class));
        }

        $parsedDsn = parse_url($dsn);
        if (false === $parsedDsn || !isset($parsedDsn['host'], $parsedDsn['user'])) {
            throw AdapterException::invalidDsn($dsn);
        }

        return SearchClient::create($parsedDsn['host'], $parsedDsn['user']);
    }
}
