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

namespace Mezcalito\UxSearchBundle\Adapter;

use Mezcalito\UxSearchBundle\Search\Facet;
use Mezcalito\UxSearchBundle\Search\Filter\FilterInterface;
use Mezcalito\UxSearchBundle\Search\Filter\RangeFilter;
use Mezcalito\UxSearchBundle\Search\Filter\TermFilter;
use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\ResultSet\FacetStat;
use Mezcalito\UxSearchBundle\Search\ResultSet\FacetTermDistribution;
use Mezcalito\UxSearchBundle\Search\SearchInterface;

abstract class AbstractAdapter implements AdapterInterface
{
    abstract public function getFacetDistributionKey(): string;

    abstract public function getFacetStatsKey(): string;

    protected function getFacets(array $results, SearchInterface $search, Query $query): array
    {
        $facetDistributionKey = $this->getFacetDistributionKey();
        $facetStatsKey = $this->getFacetStatsKey();

        $mergedFacetDistribution = array_reduce($results['results'], static function ($carry, $result) use ($facetDistributionKey) {
            if (isset($result[$facetDistributionKey])) {
                foreach ($result[$facetDistributionKey] as $facetKey => $facetValues) {
                    $carry[$facetKey] = $facetValues;
                }
            }

            return $carry;
        }, []);

        $mergedFacetStats = array_reduce($results['results'], static function ($carry, $result) use ($facetStatsKey) {
            if (isset($result[$facetStatsKey])) {
                foreach ($result[$facetStatsKey] as $facetKey => $facetStat) {
                    $carry[$facetKey] = $facetStat;
                }
            }

            return $carry;
        }, []);

        $facetsDistributions = [];

        foreach ($search->getFacets() as $facet) {
            $filter = $query->getActiveFilter($facet->getProperty());
            $facetsDistributions[$facet->getProperty()] = $this->hydrateTermDistribution($mergedFacetDistribution, $facet, $filter);

            if (!isset($mergedFacetStats[$facet->getProperty()])) {
                $mergedFacetStats[$facet->getProperty()] = ['min' => 0, 'max' => 0];
            }
        }

        foreach ($facetsDistributions as $property => $distribution) {
            if ($distribution instanceof FacetTermDistribution) {
                $values = $distribution->getValues();
                $checkedValues = $distribution->getCheckedValues();

                $checkedFacets = [];
                $uncheckedFacets = [];

                foreach ($values as $key => $value) {
                    if (\in_array($key, $checkedValues)) {
                        $checkedFacets[$key] = $value;
                    } else {
                        $uncheckedFacets[$key] = $value;
                    }
                }

                $sortedFacets = $checkedFacets + $uncheckedFacets;

                $distribution->setValues($sortedFacets);
            }
        }

        $facetStats = [];
        foreach ($mergedFacetStats as $property => $values) {
            $filter = $query->getActiveFilter($property);
            if ($filter instanceof RangeFilter) {
                $userMin = $filter->getMin();
                $userMax = $filter->getMax();
            }

            $facetStats[] = new FacetStat($property, $values['min'], $values['max'], $userMin ?? null, $userMax ?? null);
        }

        return [$facetsDistributions, $facetStats];
    }

    protected function hydrateTermDistribution(array $mergedFacetDistribution, Facet $facet, ?FilterInterface $filter): FacetTermDistribution
    {
        $values = $mergedFacetDistribution[$facet->getProperty()] ?? [];

        $termDistribution = (new FacetTermDistribution())
            ->setProperty($facet->getProperty())
            ->setValues($values)
        ;

        if ($filter instanceof TermFilter) {
            $termDistribution->setCheckedValues($filter->getValues());
        }

        return $termDistribution;
    }
}
