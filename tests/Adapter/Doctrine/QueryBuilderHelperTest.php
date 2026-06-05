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

use Doctrine\ORM\QueryBuilder;
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

    public function testTotalResultsQuery(): void
    {
        $dql = $this->helper->getTotalResultsQuery()->getQuery()->getDQL();

        $this->assertEquals('SELECT count(DISTINCT o.id) AS total FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o', $dql);
    }

    public function testTotalResultsWithFilterQuery(): void
    {
        $this->query->addActiveFilter(new TermFilter('brand', ['A', 'B']));
        $qb = $this->helper->getTotalResultsQuery();

        $dql = $qb->getQuery()->getDQL();
        $params = $qb->getParameter('o_brand_terms');

        $this->assertEquals('SELECT count(DISTINCT o.id) AS total FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o WHERE o.brand in (:o_brand_terms)', $dql);
        $this->assertEquals(['A', 'B'], $params->getValue());
    }

    public function testTotalResultsQueryWithoutCountDistinct(): void
    {
        $this->search->setResolvedAdapterParameters([
            ...$this->search->getResolvedAdapterParameters(),
            DoctrineAdapter::COUNT_DISTINCT => false,
        ]);

        $dql = $this->helper->getTotalResultsQuery()->getQuery()->getDQL();

        $this->assertEquals('SELECT count(o.id) AS total FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o', $dql);
    }

    public function testFacetTermQueryWithoutCountDistinct(): void
    {
        $this->search->setResolvedAdapterParameters([
            ...$this->search->getResolvedAdapterParameters(),
            DoctrineAdapter::COUNT_DISTINCT => false,
        ]);

        $dql = $this->helper->getFacetTermQuery($this->search->getFacet('o.brand'))->getQuery()->getDQL();

        $this->assertEquals('SELECT o.brand as value, count(o.id) AS total FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o GROUP BY o.brand ORDER BY total desc', $dql);
    }

    public function testResultsQuery(): void
    {
        $dql = $this->helper->getResultsQuery()->getQuery()->getDQL();

        $this->assertEquals('SELECT o FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o ORDER BY o.price asc', $dql);
    }

    public function testResultsQueryWithStringQuery(): void
    {
        $this->search->setResolvedAdapterParameters([
            ...$this->search->getResolvedAdapterParameters(),
            DoctrineAdapter::SEARCH_FIELDS => ['o.name', 'o.description'],
        ]);
        $this->query->setQueryString('search');

        $dql = $this->helper->getResultsQuery()->getQuery()->getDQL();

        $this->assertEquals('SELECT o FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o WHERE o.name like :queryString OR o.description like :queryString ORDER BY o.price asc', $dql);
    }

    public function testResultsQueryWithFilter(): void
    {
        $this->query->addActiveFilter(new RangeFilter('price', 10, 100));
        $dql = $this->helper->getResultsQuery()->getQuery()->getDQL();

        $this->assertEquals('SELECT o FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o WHERE o.price <= :o_price_max  AND o.price >= :o_price_min ORDER BY o.price asc', $dql);
    }

    public function testFacetTermQuery(): void
    {
        $this->query->addActiveFilter(new TermFilter('brand', ['A', 'B']));
        $this->query->addActiveFilter(new RangeFilter('price', 10, 100));

        $dql = $this->helper->getFacetTermQuery($this->search->getFacet('o.brand'))->getQuery()->getDQL();

        $this->assertEquals('SELECT o.brand as value, count(DISTINCT o.id) AS total FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o WHERE o.brand in (:o_brand_terms) AND o.price <= :o_price_max  AND o.price >= :o_price_min GROUP BY o.brand ORDER BY total desc', $dql);
    }

    public function testFacetStatsQuery(): void
    {
        $this->query->addActiveFilter(new TermFilter('brand', ['A', 'B']));
        $this->query->addActiveFilter(new RangeFilter('price', 10, 100));

        $dql = $this->helper->getFacetStatsQuery($this->search->getFacet('o.price'))->getQuery()->getDQL();

        $this->assertEquals('SELECT min(o.price) as min, max(o.price) AS max FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o WHERE o.brand in (:o_brand_terms) AND o.price <= :o_price_max  AND o.price >= :o_price_min', $dql);
    }

    public function testFacetTermSubEntityQuery(): void
    {
        $this->query->addActiveFilter(new TermFilter('bar.name', ['A']));

        $dql = $this->helper->getFacetTermQuery($this->search->getFacet('bar.name'))->getQuery()->getDQL();

        $this->assertEquals('SELECT bar.name as value, count(DISTINCT o.id) AS total FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o LEFT JOIN o.bar bar GROUP BY bar.name ORDER BY total desc', $dql);
    }

    public function testResultsQueryIgnoresInvalidSort(): void
    {
        $this->query->setActiveSort('o.id; DROP TABLE users--:asc');

        $dql = $this->helper->getResultsQuery()->getQuery()->getDQL();

        $this->assertEquals('SELECT o FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o', $dql);
    }

    public function testResultsQueryIgnoresNonWhitelistedSort(): void
    {
        $this->query->setActiveSort('o.secret_field:asc');

        $dql = $this->helper->getResultsQuery()->getQuery()->getDQL();

        $this->assertEquals('SELECT o FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o', $dql);
    }

    public function testResultsQueryAcceptsValidSort(): void
    {
        $this->query->setActiveSort('o.price:desc');

        $dql = $this->helper->getResultsQuery()->getQuery()->getDQL();

        $this->assertEquals('SELECT o FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o ORDER BY o.price desc', $dql);
    }

    public function testResultsQueryWithZeroMinValueRangeFilter(): void
    {
        $this->query->addActiveFilter(new RangeFilter('price', 0, 100));
        $qb = $this->helper->getResultsQuery();
        $dql = $qb->getQuery()->getDQL();

        $this->assertEquals('SELECT o FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o WHERE o.price <= :o_price_max  AND o.price >= :o_price_min ORDER BY o.price asc', $dql);
        $this->assertEquals(0, $qb->getParameter('o_price_min')->getValue());
        $this->assertEquals(100, $qb->getParameter('o_price_max')->getValue());
    }

    public function testResultsQueryWithZeroMaxValueRangeFilter(): void
    {
        $this->query->addActiveFilter(new RangeFilter('price', -10, 0));
        $qb = $this->helper->getResultsQuery();
        $dql = $qb->getQuery()->getDQL();

        $this->assertEquals('SELECT o FROM Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine\Foo o WHERE o.price <= :o_price_max  AND o.price >= :o_price_min ORDER BY o.price asc', $dql);
        $this->assertEquals(-10, $qb->getParameter('o_price_min')->getValue());
        $this->assertEquals(0, $qb->getParameter('o_price_max')->getValue());
    }

    public function testQueryStringPreservesExistingWhereConditions(): void
    {
        $this->search->setResolvedAdapterParameters([
            ...$this->search->getResolvedAdapterParameters(),
            DoctrineAdapter::SEARCH_FIELDS => ['o.brand'],
            DoctrineAdapter::QUERY_BUILDER => static function (QueryBuilder $qb) {
                $qb->andWhere('o.type = :type')
                    ->setParameter('type', 'vinyl');
            },
        ]);
        $this->query->setQueryString('test');
        $this->helper = new QueryBuilderHelper($this->entityManager, $this->query, $this->search);

        $qb = $this->helper->getResultsQuery();
        $dql = $qb->getQuery()->getDQL();

        $this->assertStringContainsString('o.type = :type', $dql);
        $this->assertStringContainsString('o.brand like :queryString', $dql);
        $this->assertEquals('vinyl', $qb->getParameter('type')->getValue());
        $this->assertEquals('%test%', $qb->getParameter('queryString')->getValue());
    }

    public function testQueryStringPreservesMultipleExistingWhereParameters(): void
    {
        $this->search->setResolvedAdapterParameters([
            ...$this->search->getResolvedAdapterParameters(),
            DoctrineAdapter::SEARCH_FIELDS => ['o.brand'],
            DoctrineAdapter::QUERY_BUILDER => static function (QueryBuilder $qb) {
                $qb->andWhere('o.type = :type')
                    ->andWhere('o.price > :minPrice')
                    ->setParameter('type', 'vinyl')
                    ->setParameter('minPrice', 10);
            },
        ]);
        $this->query->setQueryString('search');
        $this->helper = new QueryBuilderHelper($this->entityManager, $this->query, $this->search);

        $qb = $this->helper->getResultsQuery();
        $dql = $qb->getQuery()->getDQL();

        $this->assertStringContainsString('o.type = :type', $dql);
        $this->assertStringContainsString('o.price > :minPrice', $dql);
        $this->assertStringContainsString('o.brand like :queryString', $dql);
        $this->assertEquals('vinyl', $qb->getParameter('type')->getValue());
        $this->assertEquals(10, $qb->getParameter('minPrice')->getValue());
        $this->assertEquals('%search%', $qb->getParameter('queryString')->getValue());
    }

    public function testQueryStringWithExistingWhereAndFilters(): void
    {
        $this->search->setResolvedAdapterParameters([
            ...$this->search->getResolvedAdapterParameters(),
            DoctrineAdapter::SEARCH_FIELDS => ['o.brand'],
            DoctrineAdapter::QUERY_BUILDER => static function (QueryBuilder $qb) {
                $qb->andWhere('o.type = :type')
                    ->setParameter('type', 'vinyl');
            },
        ]);
        $this->query->setQueryString('search');
        $this->query->addActiveFilter(new RangeFilter('price', 10, 100));

        $this->helper = new QueryBuilderHelper($this->entityManager, $this->query, $this->search);

        $qb = $this->helper->getResultsQuery();
        $dql = $qb->getQuery()->getDQL();

        $this->assertStringContainsString('o.type = :type', $dql);
        $this->assertStringContainsString('o.brand like :queryString', $dql);
        $this->assertStringContainsString('o.price <= :o_price_max', $dql);
        $this->assertStringContainsString('o.price >= :o_price_min', $dql);
        $this->assertEquals('vinyl', $qb->getParameter('type')->getValue());
        $this->assertEquals('%search%', $qb->getParameter('queryString')->getValue());
    }

    public function testTotalResultsQueryPreservesExistingWhereConditions(): void
    {
        $this->search->setResolvedAdapterParameters([
            ...$this->search->getResolvedAdapterParameters(),
            DoctrineAdapter::SEARCH_FIELDS => ['o.brand'],
            DoctrineAdapter::QUERY_BUILDER => static function (QueryBuilder $qb) {
                $qb->andWhere('o.type = :type')
                    ->setParameter('type', 'vinyl');
            },
        ]);
        $this->query->setQueryString('test');
        $this->helper = new QueryBuilderHelper($this->entityManager, $this->query, $this->search);

        $qb = $this->helper->getTotalResultsQuery();
        $dql = $qb->getQuery()->getDQL();

        $this->assertStringContainsString('o.type = :type', $dql);
        $this->assertStringContainsString('o.brand like :queryString', $dql);
        $this->assertEquals('vinyl', $qb->getParameter('type')->getValue());
        $this->assertEquals('%test%', $qb->getParameter('queryString')->getValue());
    }
}
