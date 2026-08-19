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

namespace Mezcalito\UxSearchBundle\Search;

use Mezcalito\UxSearchBundle\Exception\SearchException;
use Psr\Container\ContainerInterface;

readonly class SearchProvider
{
    /**
     * @param ContainerInterface|iterable<string, SearchInterface> $searches
     */
    public function __construct(
        private ContainerInterface|iterable $searches,
    ) {
    }

    public function getSearch(string $name): SearchInterface
    {
        if ($this->searches instanceof ContainerInterface) {
            if (!$this->searches->has($name)) {
                throw SearchException::nameNotFound($name);
            }

            /* @var SearchInterface */
            return $this->searches->get($name);
        }

        foreach ($this->searches as $searchName => $search) {
            if ($name === $searchName) {
                return $search;
            }
        }

        throw SearchException::nameNotFound($name);
    }
}
