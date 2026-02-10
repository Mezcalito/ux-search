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

namespace Mezcalito\UxSearchBundle\Adapter\Algolia;

use Mezcalito\UxSearchBundle\Exception\UnsupportedFilterException;
use Mezcalito\UxSearchBundle\Search\Filter\FilterInterface;
use Mezcalito\UxSearchBundle\Search\Filter\RangeFilter;
use Mezcalito\UxSearchBundle\Search\Filter\TermFilter;
use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\SearchInterface;

class QueryBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function build(Query $query, SearchInterface $search): array
    {
        $indexName = $search->getIndexName();
        $options = $search->getResolvedAdapterParameters();

        $hitsPerPage = $query->getActiveHitsPerPage();
        $queries = ['requests' => []];

        if ($query->getActiveSort()) {
            $indexName = $query->getActiveSort();
        }

        $displayedFacets = [];

        foreach ($search->getFacets() as $facet) {
            $displayedFacets[] = $facet->getProperty();
        }

        $algoliaQuery = array_merge($options, [
            'indexName' => $indexName,
            'getRankingInfo' => true,
            'query' => $query->getQueryString(),
            'filters' => $this->formatFilters($query->getActiveFilters()),
            'hitsPerPage' => $hitsPerPage,
            'page' => ($query->getCurrentPage() - 1), // Algolia page start to 0
        ]);

        if ([] !== $displayedFacets) {
            $algoliaQuery['facets'] = $displayedFacets;
        }

        $queries['requests'][] = $algoliaQuery;

        $activeFilters = $query->getActiveFilters();

        foreach ($activeFilters as $activeFilter) {
            $otherFilters = [];
            foreach ($activeFilters as $filter) {
                if ($filter->getProperty() !== $activeFilter->getProperty()) {
                    $otherFilters[] = $filter;
                }
            }

            $queries['requests'][] = [
                'indexName' => $indexName,
                'query' => $query->getQueryString(),
                'facets' => [$activeFilter->getProperty()],
                'filters' => $this->formatFilters($otherFilters),
            ];
        }

        return $queries;
    }

    /**
     * @param FilterInterface[] $filters
     */
    private function formatFilters(array $filters): string
    {
        $formated = [];
        foreach ($filters as $filter) {
            switch ($filter::class) {
                case TermFilter::class:
                    $or = [];
                    foreach ($filter->getValues() as $value) {
                        $or[] = \sprintf('%s:"%s"', $filter->getProperty(), addslashes((string) $value));
                    }

                    $formated[] = implode(' OR ', $or);
                    break;
                case RangeFilter::class:
                    if (null !== $filter->getMin()) {
                        $formated[] = \sprintf('%s >= %s', $filter->getProperty(), $filter->getMin());
                    }

                    if (null !== $filter->getMax()) {
                        $formated[] = \sprintf('%s <= %s', $filter->getProperty(), $filter->getMax());
                    }

                    break;
                default:
                    throw UnsupportedFilterException::filterNotSupported($filter::class);
            }
        }

        return implode(' AND ', $formated);
    }
}
