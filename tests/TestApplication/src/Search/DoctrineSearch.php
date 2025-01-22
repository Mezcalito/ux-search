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

namespace Mezcalito\UxSearchBundle\Tests\TestApplication\Search;

use Doctrine\ORM\QueryBuilder;
use Mezcalito\UxSearchBundle\Adapter\Doctrine\DoctrineAdapter;
use Mezcalito\UxSearchBundle\Attribute\AsSearch;
use Mezcalito\UxSearchBundle\Search\AbstractSearch;
use Mezcalito\UxSearchBundle\Tests\TestApplication\Entity\Product;
use Mezcalito\UxSearchBundle\Twig\Components\Facet\RangeInput;

#[AsSearch(Product::class, adapter: 'doctrine')]
class DoctrineSearch extends AbstractSearch
{
    public function build(array $options = []): void
    {
        $this
            ->setAdapterParameters([
                DoctrineAdapter::MAX_FACET_VALUES_PARAM => 30,
                DoctrineAdapter::QUERY_BUILDER_ALIAS => 'o',
                DoctrineAdapter::QUERY_BUILDER => function (QueryBuilder $queryBuilder) {
                    $queryBuilder->andWhere('1 = 1');
                },
                DoctrineAdapter::SEARCH_FIELDS => ['o.name', 'o.brand'],
            ])
            ->addFacet('o.type', 'Type')
            ->addFacet('o.brand', 'Brand')
            ->addFacet('o.rating', 'Rating')
            ->addFacet('o.priceRange', 'Price range')
            ->addFacet('o.price', 'Price', RangeInput::class)
            ->addAvailableSort('o.price:asc', 'Price ↑')
            ->addAvailableSort('o.price:desc', 'Price ↓')
            ->addAvailableSort('o.popularity:asc', 'Popularity ↑')
            ->addAvailableSort('o.popularity:desc', 'Popularity ↓')
            ->enableUrlRewriting()
        ;
    }
}
