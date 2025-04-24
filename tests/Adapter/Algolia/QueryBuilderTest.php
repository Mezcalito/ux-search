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

namespace Mezcalito\UxSearchBundle\Tests\Adapter\Algolia;

use Mezcalito\UxSearchBundle\Adapter\Algolia\QueryBuilder;
use Mezcalito\UxSearchBundle\Search\Filter\RangeFilter;
use Mezcalito\UxSearchBundle\Search\Filter\TermFilter;
use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\SearchInterface;
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
{
    public function testBuildWithActiveFilters(): void
    {
        $query = $this->createMock(Query::class);
        $search = $this->createMock(SearchInterface::class);

        $query->method('getActiveFilters')->willReturn([
            new TermFilter('brand', ['Apple', 'Samsung']),
            new RangeFilter('price', 100, 500),
        ]);

        $query->method('getActiveHitsPerPage')->willReturn(10);
        $query->method('getCurrentPage')->willReturn(2);
        $query->method('getQueryString')->willReturn('smartphone');

        $search->method('getIndexName')->willReturn('products');
        $search->method('getFacets')->willReturn([
            new TermFilter('brand', []),
            new RangeFilter('price', null, null),
        ]);
        $search->method('getResolvedAdapterParameters')->willReturn(['someOption' => 'value']);

        $queryBuilder = new QueryBuilder();

        $result = $queryBuilder->build($query, $search);

        $this->assertIsArray($result);
        $this->assertCount(3, $result); // 1 main query + 2 facet queries
        $this->assertEquals('products', $result[0]['indexName']);
        $this->assertStringContainsString('brand:"Apple" OR brand:"Samsung" AND price >= 100 AND price <= 500', $result[0]['filters']);
    }

    public function testBuildWithActiveSorting(): void
    {
        $query = $this->createMock(Query::class);
        $search = $this->createMock(SearchInterface::class);

        $query->method('getActiveSort')->willReturn('products_price_asc');
        $search->method('getIndexName')->willReturn('products');
        $search->method('getResolvedAdapterParameters')->willReturn([]);

        $queryBuilder = new QueryBuilder();

        $result = $queryBuilder->build($query, $search);

        $this->assertEquals('products_price_asc', $result[0]['indexName']);
    }

    public function testBuildWithNoFilters(): void
    {
        $query = $this->createMock(Query::class);
        $search = $this->createMock(SearchInterface::class);

        $query->method('getActiveFilters')->willReturn([]);
        $query->method('getCurrentPage')->willReturn(1);
        $search->method('getIndexName')->willReturn('products');

        $queryBuilder = new QueryBuilder();

        $result = $queryBuilder->build($query, $search);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('products', $result[0]['indexName']);
    }
}
