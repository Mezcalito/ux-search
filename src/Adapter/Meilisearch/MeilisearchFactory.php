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

namespace Mezcalito\UxSearchBundle\Adapter\Meilisearch;

use Meilisearch\Client;
use Mezcalito\UxSearchBundle\Adapter\AdapterFactoryInterface;
use Mezcalito\UxSearchBundle\Adapter\AdapterInterface;

readonly class MeilisearchFactory implements AdapterFactoryInterface
{
    public function support(string $dsn): bool
    {
        return str_starts_with($dsn, 'meilisearch');
    }

    public function createAdapter(string $dsn): AdapterInterface
    {
        return new MeilisearchAdapter($this->createClient($dsn), new QueryBuilder());
    }

    public function createClient(string $dsn): Client
    {
        if (!class_exists(Client::class)) {
            throw new \LogicException(\sprintf('You cannot use the "%s" as Meilisearch Client is not installed. Try running "composer require meilisearch/meilisearch-php".', self::class));
        }

        $parsedDsn = parse_url($dsn);
        parse_str($parsedDsn['query'] ?? '', $params);

        $tls = !isset($params['tls']) || 'true' === $params['tls'];

        $url = \sprintf('%s://%s:%s',
            $tls ? 'https' : 'http',
            $parsedDsn['host'] ?? 'localhost',
            $parsedDsn['port'] ?? '7700',
        );

        return new Client($url, $parsedDsn['user'] ?? null);
    }
}
