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

namespace Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine;

use Mezcalito\UxSearchBundle\Attribute\AsSearch;
use Mezcalito\UxSearchBundle\Search\AbstractSearch;
use Mezcalito\UxSearchBundle\Twig\Components\Facet\RangeInput;

#[AsSearch(Foo::class)]
class FooSearch extends AbstractSearch
{
    public function build(array $options = []): void
    {
        $this
            ->addFacet('o.type', 'Type')
            ->addFacet('o.brand', 'Brand')
            ->addFacet('o.price', 'Price', RangeInput::class)
            ->addAvailableSort('o.price:asc', 'Price ↑')
            ->addAvailableSort('o.price:desc', 'Price ↓')
        ;
    }
}
