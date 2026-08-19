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

namespace Mezcalito\UxSearchBundle\Tests\Adapter;

use Mezcalito\UxSearchBundle\Adapter\AbstractAdapter;
use Mezcalito\UxSearchBundle\Search\AbstractSearch;
use Mezcalito\UxSearchBundle\Search\Filter\RangeFilter;
use Mezcalito\UxSearchBundle\Search\Filter\TermFilter;
use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\ResultSet\FacetStat;
use Mezcalito\UxSearchBundle\Search\ResultSet\ResultSet;
use Mezcalito\UxSearchBundle\Search\SearchInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AbstractAdapterTest extends TestCase
{
    public function testFacetsAreMergedAcrossResults(): void
    {
        $search = $this->createSearch();
        $query = new Query();

        $results = [
            'results' => [
                ['facets' => ['category' => ['books' => 3, 'movies' => 2]]],
                ['facets' => ['brand' => ['acme' => 5]], 'facets_stats' => ['price' => ['min' => 10, 'max' => 90]]],
            ],
        ];

        [$distributions, $stats] = $this->createAdapter()->exposeGetFacets($results, $search, $query);

        $this->assertSame(['category', 'brand', 'price'], array_keys($distributions));
        $this->assertSame(['books' => 3, 'movies' => 2], $distributions['category']->getValues());
        $this->assertSame(['acme' => 5], $distributions['brand']->getValues());

        $statsByProperty = $this->indexStats($stats);
        $this->assertSame(10, $statsByProperty['price']->getMin());
        $this->assertSame(90, $statsByProperty['price']->getMax());
    }

    public function testMissingFacetGetsEmptyDistributionAndZeroStats(): void
    {
        $search = $this->createSearch();
        $query = new Query();

        [$distributions, $stats] = $this->createAdapter()->exposeGetFacets(['results' => [[]]], $search, $query);

        $this->assertSame([], $distributions['category']->getValues());

        $statsByProperty = $this->indexStats($stats);
        $this->assertSame(0, $statsByProperty['category']->getMin());
        $this->assertSame(0, $statsByProperty['category']->getMax());
    }

    public function testCheckedValuesAreSortedFirst(): void
    {
        $search = $this->createSearch();
        $query = new Query();
        $query->addActiveFilter(new TermFilter('category', ['movies']));

        $results = [
            'results' => [
                ['facets' => ['category' => ['books' => 3, 'movies' => 2, 'music' => 1]]],
            ],
        ];

        [$distributions] = $this->createAdapter()->exposeGetFacets($results, $search, $query);

        $this->assertSame(['movies', 'books', 'music'], array_keys($distributions['category']->getValues()));
        $this->assertSame(['movies'], $distributions['category']->getCheckedValues());
    }

    public function testRangeFilterValuesArePropagatedToStats(): void
    {
        $search = $this->createSearch();
        $query = new Query();
        $query->addActiveFilter(new RangeFilter('price', 20.0, 50.0));

        $results = [
            'results' => [
                ['facets_stats' => ['price' => ['min' => 10, 'max' => 90]]],
            ],
        ];

        [, $stats] = $this->createAdapter()->exposeGetFacets($results, $search, $query);

        $statsByProperty = $this->indexStats($stats);
        $this->assertSame(20.0, $statsByProperty['price']->getUserMin());
        $this->assertSame(50.0, $statsByProperty['price']->getUserMax());
    }

    private function createSearch(): SearchInterface
    {
        $search = new class extends AbstractSearch {
            public function build(array $options = []): void
            {
                $this->addFacet('category', 'Category');
                $this->addFacet('brand', 'Brand');
                $this->addFacet('price', 'Price');
            }
        };

        return $search->create();
    }

    private function createAdapter(): object
    {
        return new class extends AbstractAdapter {
            public function getFacetDistributionKey(): string
            {
                return 'facets';
            }

            public function getFacetStatsKey(): string
            {
                return 'facets_stats';
            }

            public function search(Query $query, SearchInterface $search): ResultSet
            {
                return new ResultSet();
            }

            public function configureParameters(OptionsResolver $resolver): void
            {
            }

            /**
             * @param array<string, mixed> $results
             *
             * @return array{0: array<string, \Mezcalito\UxSearchBundle\Search\ResultSet\FacetTermDistribution>, 1: array<int, FacetStat>}
             */
            public function exposeGetFacets(array $results, SearchInterface $search, Query $query): array
            {
                return $this->getFacets($results, $search, $query);
            }
        };
    }

    /**
     * @param FacetStat[] $stats
     *
     * @return array<string, FacetStat>
     */
    private function indexStats(array $stats): array
    {
        $indexed = [];
        foreach ($stats as $stat) {
            $indexed[$stat->getProperty()] = $stat;
        }

        return $indexed;
    }
}
