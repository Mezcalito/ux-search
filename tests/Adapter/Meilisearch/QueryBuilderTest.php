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

namespace Mezcalito\UxSearchBundle\Tests\Adapter\Meilisearch;

use Meilisearch\Contracts\SearchQuery;
use Mezcalito\UxSearchBundle\Adapter\Meilisearch\MeilisearchAdapter;
use Mezcalito\UxSearchBundle\Adapter\Meilisearch\QueryBuilder;
use Mezcalito\UxSearchBundle\Search\Facet;
use Mezcalito\UxSearchBundle\Search\Filter\RangeFilter;
use Mezcalito\UxSearchBundle\Search\Filter\TermFilter;
use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\SearchInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
{
    private QueryBuilder $queryBuilder;

    private SearchInterface $search;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->queryBuilder = new QueryBuilder();
        $this->search = $this->createStub(SearchInterface::class);
        $this->search->method('getIndexName')->willReturn('test');
        $this->search->method('getResolvedAdapterParameters')->willReturn([
            MeilisearchAdapter::ATTRIBUTES_TO_RETRIEVE_PARAM => ['*'],
            MeilisearchAdapter::ATTRIBUTES_TO_CROP_PARAM => [],
            MeilisearchAdapter::CROP_LENGTH_PARAM => 10,
            MeilisearchAdapter::CROP_MARKER_PARAM => '...',
            MeilisearchAdapter::ATTRIBUTES_TO_HIGHLIGHT_PARAM => [],
            MeilisearchAdapter::HIGHLIGHT_PRE_TAG_PARAM => '<em>',
            MeilisearchAdapter::HIGHLIGHT_POST_TAG_PARAM => '</em>',
        ]);
    }

    public function testQueryString(): void
    {
        $query = (new Query())
            ->setQueryString('my search');

        $searchQueries = $this->queryBuilder->build($query, $this->search);

        $this->assertCount(1, $searchQueries);
        $searchQuery = $searchQueries[0];

        $this->assertInstanceOf(SearchQuery::class, $searchQuery);
        $this->assertEquals([
            'indexUid' => 'test',
            'q' => 'my search',
            'filter' => [],
            'sort' => [],
            'hitsPerPage' => 12,
            'page' => 1,
            'showRankingScore' => true,
            'attributesToRetrieve' => ['*'],
            'attributesToCrop' => [],
            'cropLength' => 10,
            'cropMarker' => '...',
            'attributesToHighlight' => [],
            'highlightPreTag' => '<em>',
            'highlightPostTag' => '</em>',
        ], $searchQuery->toArray());
    }

    public function testSort(): void
    {
        $query = (new Query())->setActiveSort('price:asc');

        $searchQueries = $this->queryBuilder->build($query, $this->search);

        $this->assertCount(1, $searchQueries);
        $searchQuery = $searchQueries[0];

        $this->assertInstanceOf(SearchQuery::class, $searchQuery);
        $this->assertEquals([
            'indexUid' => 'test',
            'filter' => [],
            'q' => '',
            'sort' => ['price:asc'],
            'hitsPerPage' => 12,
            'page' => 1,
            'showRankingScore' => true,
            'attributesToRetrieve' => ['*'],
            'attributesToCrop' => [],
            'cropLength' => 10,
            'cropMarker' => '...',
            'attributesToHighlight' => [],
            'highlightPreTag' => '<em>',
            'highlightPostTag' => '</em>',
        ], $searchQuery->toArray());
    }

    public function testPagination(): void
    {
        $query = (new Query())
            ->setCurrentPage(3)
            ->setActiveHitsPerPage(9)
        ;

        $searchQueries = $this->queryBuilder->build($query, $this->search);

        $this->assertCount(1, $searchQueries);
        $searchQuery = $searchQueries[0];

        $this->assertInstanceOf(SearchQuery::class, $searchQuery);
        $this->assertEquals([
            'indexUid' => 'test',
            'filter' => [],
            'q' => '',
            'sort' => [],
            'hitsPerPage' => 9,
            'page' => 3,
            'showRankingScore' => true,
            'attributesToRetrieve' => ['*'],
            'attributesToCrop' => [],
            'cropLength' => 10,
            'cropMarker' => '...',
            'attributesToHighlight' => [],
            'highlightPreTag' => '<em>',
            'highlightPostTag' => '</em>',
        ], $searchQuery->toArray());
    }

    public function testFacets(): void
    {
        $this->search->method('getFacets')->willReturn([
            new Facet('color', 'Color'),
            new Facet('price', 'Price'),
        ]);
        $query = new Query();

        $searchQueries = $this->queryBuilder->build($query, $this->search);

        $this->assertCount(1, $searchQueries);
        $searchQuery = $searchQueries[0];

        $this->assertInstanceOf(SearchQuery::class, $searchQuery);
        $this->assertEquals([
            'indexUid' => 'test',
            'filter' => [],
            'q' => '',
            'sort' => [],
            'hitsPerPage' => 12,
            'page' => 1,
            'showRankingScore' => true,
            'facets' => ['color', 'price'],
            'attributesToRetrieve' => ['*'],
            'attributesToCrop' => [],
            'cropLength' => 10,
            'cropMarker' => '...',
            'attributesToHighlight' => [],
            'highlightPreTag' => '<em>',
            'highlightPostTag' => '</em>',
        ], $searchQuery->toArray());
    }

    public function testFilters(): void
    {
        $this->search->method('getFacets')->willReturn([
            new Facet('color', 'Color'),
            new Facet('price', 'Price'),
        ]);

        $query = (new Query())
            ->addActiveFilter(new TermFilter('color', ['red', 'green']))
            ->addActiveFilter(new RangeFilter('price', 5, 10))
        ;

        $searchQueries = $this->queryBuilder->build($query, $this->search);

        $this->assertCount(3, $searchQueries);

        $mainSearchQuery = $searchQueries[0];

        $this->assertInstanceOf(SearchQuery::class, $mainSearchQuery);
        $this->assertEquals([
            'indexUid' => 'test',
            'filter' => [
                ['color = "red"', 'color = "green"'],
                'price >= 5',
                'price <= 10',
            ],
            'q' => '',
            'sort' => [],
            'hitsPerPage' => 12,
            'page' => 1,
            'attributesToRetrieve' => ['*'],
            'attributesToCrop' => [],
            'cropLength' => 10,
            'cropMarker' => '...',
            'attributesToHighlight' => [],
            'highlightPreTag' => '<em>',
            'highlightPostTag' => '</em>',
            'showRankingScore' => true,
            'facets' => ['color', 'price'],
        ], $mainSearchQuery->toArray());

        $colorFacetQuery = $searchQueries[1];
        $this->assertEquals([
            'indexUid' => 'test',
            'q' => '',
            'filter' => [
                'price >= 5',
                'price <= 10',
            ],
            'facets' => ['color'],
            'limit' => 0,
        ], $colorFacetQuery->toArray());

        $priceFacetQuery = $searchQueries[2];
        $this->assertEquals([
            'indexUid' => 'test',
            'q' => '',
            'filter' => [
                ['color = "red"', 'color = "green"'],
            ],
            'facets' => ['price'],
            'limit' => 0,
        ], $priceFacetQuery->toArray());
    }
}
