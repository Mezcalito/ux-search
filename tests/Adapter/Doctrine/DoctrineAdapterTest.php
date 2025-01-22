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

use Mezcalito\UxSearchBundle\Search\Filter\RangeFilter;
use Mezcalito\UxSearchBundle\Search\Filter\TermFilter;
use Mezcalito\UxSearchBundle\Search\ResultSet\ResultSet;
use Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo;

class DoctrineAdapterTest extends AbstractDoctrineTestCase
{
    public function testSearch(): void
    {
        $this->createDatabase([
            new Foo('A', '1', 10),
            new Foo('A', '1', 11),
            new Foo('B', '2', 12),
            new Foo('C', '2', 13),
        ]);

        $resultSet = $this->adapter->search($this->query, $this->search);

        $this->assertInstanceOf(ResultSet::class, $resultSet);
        $this->assertEquals(4, $resultSet->getTotalResults());
        $this->assertCount(4, $resultSet->getHits());

        $this->assertEquals([
            'A' => 2,
            'B' => 1,
            'C' => 1,
        ], $resultSet->getFacetDistribution('o.type')->getValues());

        $this->assertEquals([
            '1' => 2,
            '2' => 2,
        ], $resultSet->getFacetDistribution('o.brand')->getValues());

        $this->assertEquals(10, $resultSet->getFacetStat('o.price')->getMin());
        $this->assertEquals(13, $resultSet->getFacetStat('o.price')->getMax());
    }

    public function testSearchWithFilter(): void
    {
        $this->createDatabase([
            new Foo('A', '1', 10),
            new Foo('A', '1', 13),  // filtered
            new Foo('B', '2', 12),  // filtered
            new Foo('C', '2', 13),  // filtered
        ]);

        $this->query->addActiveFilter(new RangeFilter('o.price', 10, 12));
        $this->query->addActiveFilter(new TermFilter('o.type', ['A']));

        $resultSet = $this->adapter->search($this->query, $this->search);

        $this->assertInstanceOf(ResultSet::class, $resultSet);
        $this->assertEquals(1, $resultSet->getTotalResults());
        $this->assertCount(1, $resultSet->getHits());

        $this->assertEquals(10, $resultSet->getFacetStat('o.price')->getMin());
        $this->assertEquals(10, $resultSet->getFacetStat('o.price')->getUserMin());
        $this->assertEquals(13, $resultSet->getFacetStat('o.price')->getMax());
        $this->assertEquals(12, $resultSet->getFacetStat('o.price')->getUserMax());

        $this->assertEquals([
            'A' => 1,
            'B' => 1,
        ], $resultSet->getFacetDistribution('o.type')->getValues());

        $this->assertEquals([
            '1' => 1,
        ], $resultSet->getFacetDistribution('o.brand')->getValues());
    }
}
