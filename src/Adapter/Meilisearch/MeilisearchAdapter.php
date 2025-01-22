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

use Meilisearch\Client;
use Mezcalito\UxSearchBundle\Adapter\AdapterInterface;
use Mezcalito\UxSearchBundle\Search\Facet;
use Mezcalito\UxSearchBundle\Search\Filter\FilterInterface;
use Mezcalito\UxSearchBundle\Search\Filter\RangeFilter;
use Mezcalito\UxSearchBundle\Search\Filter\TermFilter;
use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\ResultSet\FacetStat;
use Mezcalito\UxSearchBundle\Search\ResultSet\FacetTermDistribution;
use Mezcalito\UxSearchBundle\Search\ResultSet\Hit;
use Mezcalito\UxSearchBundle\Search\ResultSet\ResultSet;
use Mezcalito\UxSearchBundle\Search\SearchInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

readonly class MeilisearchAdapter implements AdapterInterface
{
    public const string ATTRIBUTES_TO_RETRIEVE_PARAM = 'attributesToRetrieve';

    public const string ATTRIBUTES_TO_CROP_PARAM = 'attributesToCrop';

    public const string CROP_LENGTH_PARAM = 'cropLength';

    public const string CROP_MARKER_PARAM = 'cropMarker';

    public const string ATTRIBUTES_TO_HIGHLIGHT_PARAM = 'attributesToHighlight';

    public const string HIGHLIGHT_PRE_TAG_PARAM = 'highlightPreTag';

    public const string HIGHLIGHT_POST_TAG_PARAM = 'highlightPostTag';

    public function __construct(
        private Client $client,
        private QueryBuilder $queryBuilder,
    ) {
    }

    public function search(Query $query, SearchInterface $search): ResultSet
    {
        $queries = $this->queryBuilder->build($query, $search);

        $results = $this->client->multiSearch($queries);

        $resultsToProcess = $results['results'][0];

        $hits = [];
        foreach ($resultsToProcess['hits'] as $hit) {
            $hits[] = new Hit($hit, $hit['_rankingScore']);
        }

        $mergedFacetDistribution = array_reduce($results['results'], function ($carry, $result) {
            if (isset($result['facetDistribution'])) {
                foreach ($result['facetDistribution'] as $facetKey => $facetValues) {
                    $carry[$facetKey] = $facetValues;
                }
            }

            return $carry;
        }, []);

        $mergedFacetStats = array_reduce($results['results'], function ($carry, $result) {
            if (isset($result['facetStats'])) {
                foreach ($result['facetStats'] as $facetKey => $facetStat) {
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

        return (new ResultSet())
            ->setIndexUid($resultsToProcess['indexUid'])
            ->setHits($hits)
            ->setTotalResults($resultsToProcess['totalHits'])
            ->setFacetDistributions($facetsDistributions)
            ->setFacetStats($facetStats)
        ;
    }

    public function configureParameters(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            self::ATTRIBUTES_TO_RETRIEVE_PARAM => ['*'],
            self::ATTRIBUTES_TO_CROP_PARAM => [],
            self::CROP_LENGTH_PARAM => 10,
            self::CROP_MARKER_PARAM => '...',
            self::ATTRIBUTES_TO_HIGHLIGHT_PARAM => [],
            self::HIGHLIGHT_PRE_TAG_PARAM => '<em>',
            self::HIGHLIGHT_POST_TAG_PARAM => '</em>',
        ]);

        $resolver->setAllowedTypes(self::ATTRIBUTES_TO_RETRIEVE_PARAM, 'string[]');
        $resolver->setAllowedTypes(self::ATTRIBUTES_TO_CROP_PARAM, 'string[]');
        $resolver->setAllowedTypes(self::CROP_LENGTH_PARAM, 'int');
        $resolver->setAllowedTypes(self::CROP_MARKER_PARAM, 'string');
        $resolver->setAllowedTypes(self::ATTRIBUTES_TO_HIGHLIGHT_PARAM, 'string[]');
        $resolver->setAllowedTypes(self::HIGHLIGHT_PRE_TAG_PARAM, 'string');
        $resolver->setAllowedTypes(self::HIGHLIGHT_POST_TAG_PARAM, 'string');
    }

    private function hydrateTermDistribution(array $mergedFacetDistribution, Facet $facet, ?FilterInterface $filter): FacetTermDistribution
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
