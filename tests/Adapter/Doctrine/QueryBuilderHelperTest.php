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

use Mezcalito\UxSearchBundle\Adapter\Doctrine\DoctrineAdapter;
use Mezcalito\UxSearchBundle\Adapter\Doctrine\QueryBuilderHelper;
use Mezcalito\UxSearchBundle\Search\Filter\RangeFilter;
use Mezcalito\UxSearchBundle\Search\Filter\TermFilter;

class QueryBuilderHelperTest extends AbstractDoctrineTestCase
{
    private QueryBuilderHelper $helper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->helper = new QueryBuilderHelper($this->entityManager, $this->query, $this->search);
    }

    public function testTotalResultsQuery()
    {
        $dql = $this->helper->getTotalResultsQuery()->getQuery()->getDQL();

        $this->assertEquals('SELECT count(o.id) AS total FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o', $dql);
    }

    public function testTotalResultsWithFilterQuery()
    {
        $this->query->addActiveFilter(new TermFilter('o.brand', ['A', 'B']));
        $qb = $this->helper->getTotalResultsQuery();

        $dql = $qb->getQuery()->getDQL();
        $params = $qb->getParameter('o_brand_terms');

        $this->assertEquals('SELECT count(o.id) AS total FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o WHERE o.brand in (:o_brand_terms)', $dql);
        $this->assertEquals(['A', 'B'], $params->getValue());
    }

    public function testResultsQuery()
    {
        $dql = $this->helper->getResultsQuery()->getQuery()->getDQL();

        $this->assertEquals('SELECT o FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o ORDER BY o.price asc', $dql);
    }

    public function testResultsQueryWithStringQuery()
    {
        $this->search->setResolvedAdapterParameters([
            ...$this->search->getResolvedAdapterParameters(),
            DoctrineAdapter::SEARCH_FIELDS => ['o.name', 'o.description'],
        ]);
        $this->query->setQueryString('search');

        $dql = $this->helper->getResultsQuery()->getQuery()->getDQL();

        $this->assertEquals('SELECT o FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o WHERE o.name like :queryString OR o.description like :queryString ORDER BY o.price asc', $dql);
    }

    public function testResultsQueryWithFilter()
    {
        $this->query->addActiveFilter(new RangeFilter('o.price', 10, 100));
        $dql = $this->helper->getResultsQuery()->getQuery()->getDQL();

        $this->assertEquals('SELECT o FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o WHERE o.price <= :o_price_max  AND o.price >= :o_price_min ORDER BY o.price asc', $dql);
    }

    public function testFacetTermQuery()
    {
        $this->query->addActiveFilter(new TermFilter('o.brand', ['A', 'B']));
        $this->query->addActiveFilter(new RangeFilter('o.price', 10, 100));

        $dql = $this->helper->getFacetTermQuery($this->search->getFacet('o.brand'))->getQuery()->getDQL();

        $this->assertEquals('SELECT o.brand as value, count(o.brand) AS total FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o WHERE o.price <= :o_price_max  AND o.price >= :o_price_min GROUP BY o.brand ORDER BY total desc', $dql);
    }

    public function testFacetStatsQuery()
    {
        $this->query->addActiveFilter(new TermFilter('o.brand', ['A', 'B']));
        $this->query->addActiveFilter(new RangeFilter('o.price', 10, 100));

        $dql = $this->helper->getFacetStatsQuery($this->search->getFacet('o.price'))->getQuery()->getDQL();

        $this->assertEquals('SELECT min(o.price) as min, max(o.price) AS max FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o WHERE o.brand in (:o_brand_terms)', $dql);
    }
}
