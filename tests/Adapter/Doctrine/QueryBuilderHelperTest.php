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

        $this->assertEquals('SELECT count(DISTINCT (o.id)) AS total FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o', $dql);
    }

    public function testTotalResultsWithFilterQuery()
    {
        $this->query->addActiveFilter(new TermFilter('brand', ['A', 'B']));
        $qb = $this->helper->getTotalResultsQuery();

        $dql = $qb->getQuery()->getDQL();
        $params = $qb->getParameter('o_brand_terms');

        $this->assertEquals('SELECT count(DISTINCT (o.id)) AS total FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o WHERE o.brand in (:o_brand_terms)', $dql);
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
        $this->query->addActiveFilter(new RangeFilter('price', 10, 100));
        $dql = $this->helper->getResultsQuery()->getQuery()->getDQL();

        $this->assertEquals('SELECT o FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o WHERE o.price <= :o_price_max  AND o.price >= :o_price_min ORDER BY o.price asc', $dql);
    }

    public function testFacetTermQuery()
    {
        $this->query->addActiveFilter(new TermFilter('brand', ['A', 'B']));
        $this->query->addActiveFilter(new RangeFilter('price', 10, 100));

        $dql = $this->helper->getFacetTermQuery($this->search->getFacet('o.brand'))->getQuery()->getDQL();

        $this->assertEquals('SELECT o.brand as value, count(o.brand) AS total FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o WHERE o.brand in (:o_brand_terms) AND o.price <= :o_price_max  AND o.price >= :o_price_min GROUP BY o.brand ORDER BY total desc', $dql);
    }

    public function testFacetStatsQuery()
    {
        $this->query->addActiveFilter(new TermFilter('brand', ['A', 'B']));
        $this->query->addActiveFilter(new RangeFilter('price', 10, 100));

        $dql = $this->helper->getFacetStatsQuery($this->search->getFacet('o.price'))->getQuery()->getDQL();

        $this->assertEquals('SELECT min(o.price) as min, max(o.price) AS max FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o WHERE o.brand in (:o_brand_terms) AND o.price <= :o_price_max  AND o.price >= :o_price_min', $dql);
    }

    public function testFacetTermSubEntityQuery()
    {
        $this->query->addActiveFilter(new TermFilter('bar.name', ['A']));

        $dql = $this->helper->getFacetTermQuery($this->search->getFacet('bar.name'))->getQuery()->getDQL();

        $this->assertEquals('SELECT bar.name as value, count(bar.name) AS total FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o LEFT JOIN o.bar bar GROUP BY bar.name ORDER BY total desc', $dql);
    }

    public function testResultsQueryIgnoresInvalidSort()
    {
        $this->query->setActiveSort('o.id; DROP TABLE users--:asc');

        $dql = $this->helper->getResultsQuery()->getQuery()->getDQL();

        $this->assertEquals('SELECT o FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o', $dql);
    }

    public function testResultsQueryIgnoresNonWhitelistedSort()
    {
        $this->query->setActiveSort('o.secret_field:asc');

        $dql = $this->helper->getResultsQuery()->getQuery()->getDQL();

        $this->assertEquals('SELECT o FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o', $dql);
    }

    public function testResultsQueryAcceptsValidSort()
    {
        $this->query->setActiveSort('o.price:desc');

        $dql = $this->helper->getResultsQuery()->getQuery()->getDQL();

        $this->assertEquals('SELECT o FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o ORDER BY o.price desc', $dql);
    }

    public function testResultsQueryWithZeroMinValueRangeFilter()
    {
        $this->query->addActiveFilter(new RangeFilter('price', 0, 100));
        $qb = $this->helper->getResultsQuery();
        $dql = $qb->getQuery()->getDQL();

        $this->assertEquals('SELECT o FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o WHERE o.price <= :o_price_max  AND o.price >= :o_price_min ORDER BY o.price asc', $dql);
        $this->assertEquals(0, $qb->getParameter('o_price_min')->getValue());
        $this->assertEquals(100, $qb->getParameter('o_price_max')->getValue());
    }

    public function testResultsQueryWithZeroMaxValueRangeFilter()
    {
        $this->query->addActiveFilter(new RangeFilter('price', -10, 0));
        $qb = $this->helper->getResultsQuery();
        $dql = $qb->getQuery()->getDQL();

        $this->assertEquals('SELECT o FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o WHERE o.price <= :o_price_max  AND o.price >= :o_price_min ORDER BY o.price asc', $dql);
        $this->assertEquals(-10, $qb->getParameter('o_price_min')->getValue());
        $this->assertEquals(0, $qb->getParameter('o_price_max')->getValue());
    }
}
