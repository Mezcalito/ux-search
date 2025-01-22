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

namespace Mezcalito\UxSearchBundle\Adapter\Meilisearch;

use Meilisearch\Contracts\SearchQuery;
use Mezcalito\UxSearchBundle\Search\Filter\FilterInterface;
use Mezcalito\UxSearchBundle\Search\Filter\RangeFilter;
use Mezcalito\UxSearchBundle\Search\Filter\TermFilter;
use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\SearchInterface;

class QueryBuilder
{
    public function build(Query $query, SearchInterface $search): array
    {
        $options = $search->getResolvedAdapterParameters();

        $hitsPerPage = $query->getActiveHitsPerPage();
        $queries = [];

        $formatedSorting = $query->getActiveSort() ? [$query->getActiveSort()] : [];
        $displayedFacets = [];

        foreach ($search->getFacets() as $facet) {
            $displayedFacets[] = $facet->getProperty();
        }

        $indexName = $search->getIndexName();

        $meilisearchQuery = (new SearchQuery())
            ->setIndexUid($indexName)
            ->setQuery($query->getQueryString())
            ->setFilter($this->formatFilters($query->getActiveFilters()))
            ->setSort($formatedSorting)
            ->setShowRankingScore(true)
            ->setHitsPerPage($hitsPerPage)
            ->setPage($query->getCurrentPage())
            ->setAttributesToRetrieve($options['attributesToRetrieve'])
            ->setAttributesToCrop($options['attributesToCrop'])
            ->setCropLength($options['cropLength'])
            ->setCropMarker($options['cropMarker'])
            ->setAttributesToHighlight($options['attributesToHighlight'])
            ->setHighlightPreTag($options['highlightPreTag'])
            ->setHighlightPostTag($options['highlightPostTag'])
        ;

        if ([] !== $displayedFacets) {
            $meilisearchQuery->setFacets($displayedFacets);
        }

        $queries[] = $meilisearchQuery;

        $activeFilters = $query->getActiveFilters();

        foreach ($activeFilters as $activeFilter) {
            $otherFilters = [];
            foreach ($activeFilters as $filter) {
                if ($filter->getProperty() !== $activeFilter->getProperty()) {
                    $otherFilters[] = $filter;
                }
            }

            $queries[] = (new SearchQuery())
                ->setIndexUid($indexName)
                ->setQuery($query->getQueryString())
                ->setFacets([$activeFilter->getProperty()])
                ->setFilter($this->formatFilters($otherFilters))
                ->setLimit(0);
        }

        return $queries;
    }

    /**
     * @param FilterInterface[] $filters
     */
    private function formatFilters(array $filters): array
    {
        $formated = [];
        foreach ($filters as $filter) {
            switch ($filter::class) {
                case TermFilter::class:
                    $or = [];
                    foreach ($filter->getValues() as $value) {
                        $or[] = \sprintf('%s = "%s"', $filter->getProperty(), addslashes((string) $value));
                    }

                    $formated[] = $or;
                    break;
                case RangeFilter::class:
                    if ($filter->getMin()) {
                        $formated[] = \sprintf('%s >= %d', $filter->getProperty(), $filter->getMin());
                    }

                    if ($filter->getMax()) {
                        $formated[] = \sprintf('%s <= %d', $filter->getProperty(), $filter->getMax());
                    }

                    break;
                default:
                    throw new \Exception(\sprintf('Facet filter "%s" not supported', $filter::class));
            }
        }

        return $formated;
    }
}
