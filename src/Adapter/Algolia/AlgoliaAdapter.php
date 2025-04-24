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

use Algolia\AlgoliaSearch\SearchClient;
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

readonly class AlgoliaAdapter implements AdapterInterface
{
    public const string ADVANCED_SYNTAX_PARAM = 'advancedSyntax';

    public const string ADVANCED_SYNTAX_FEATURES_PARAM = 'advancedSyntaxFeatures';

    public const string ALLOW_TYPOS_ON_NUMERIC_TOKENS_PARAM = 'allowTyposOnNumericTokens';

    public const string ALTERNATIVES_AS_EXACT_PARAM = 'alternativesAsExact';

    public const string ANALYTICS_PARAM = 'analytics';

    public const string ANALYTICS_TAGS_PARAM = 'analyticsTags';

    public const string ATTRIBUTES_TO_HIGHLIGHT_PARAM = 'attributesToHighlight';

    public const string ATTRIBUTES_TO_RETRIEVE_PARAM = 'attributesToRetrieve';

    public const string DECOMPOUND_QUERY_PARAM = 'decompoundQuery';

    public const string DISABLE_EXACT_ON_ATTRIBUTES_PARAM = 'disableExactOnAttributes';

    public const string DISABLE_TYPO_TOLERANCE_ON_ATTRIBUTES_PARAM = 'disableTypoToleranceOnAttributes';

    public const string ENABLE_AB_TEST_PARAM = 'enableABTest';

    public const string ENABLE_PERSONALIZATION_PARAM = 'enablePersonalization';

    public const string HIGHLIGHT_POST_TAG_PARAM = 'highlightPostTag';

    public const string HIGHLIGHT_PRE_TAG_PARAM = 'highlightPreTag';

    public const string MAX_VALUES_PER_FACET_PARAM = 'maxValuesPerFacet';

    public const string QUERY_TYPE_PARAM = 'queryType';

    public const string SORT_FACET_VALUES_BY_PARAM = 'sortFacetValuesBy';

    public const string SYNONYMS_PARAM = 'synonyms';

    public function __construct(
        private SearchClient $client,
        private QueryBuilder $queryBuilder,
    ) {
    }

    public function search(Query $query, SearchInterface $search): ResultSet
    {
        $queries = $this->queryBuilder->build($query, $search);

        $results = $this->client->search($queries);

        $resultsToProcess = $results['results'][0];

        $hits = [];
        foreach ($resultsToProcess['hits'] as $hit) {
            $hits[] = new Hit($hit, $hit['_rankingInfo']['userScore']);
        }

        $mergedFacets = array_reduce($results['results'], function ($carry, $result) {
            if (isset($result['facets'])) {
                foreach ($result['facets'] as $facetKey => $facetValues) {
                    $carry[$facetKey] = $facetValues;
                }
            }

            return $carry;
        }, []);

        $mergedFacetStats = array_reduce($results['results'], function ($carry, $result) {
            if (isset($result['facets_stats'])) {
                foreach ($result['facets_stats'] as $facetKey => $facetStat) {
                    $carry[$facetKey] = $facetStat;
                }
            }

            return $carry;
        }, []);

        $facetsDistributions = [];

        foreach ($search->getFacets() as $facet) {
            $filter = $query->getActiveFilter($facet->getProperty());
            $facetsDistributions[$facet->getProperty()] = $this->hydrateTermDistribution($mergedFacets, $facet, $filter);

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
            ->setIndexUid($resultsToProcess['index'])
            ->setHits($hits)
            ->setTotalResults($resultsToProcess['nbHits'])
            ->setFacetDistributions($facetsDistributions)
            ->setFacetStats($facetStats)
        ;
    }

    public function configureParameters(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            self::ADVANCED_SYNTAX_PARAM => false,
            self::ADVANCED_SYNTAX_FEATURES_PARAM => ['exactPhrase', 'excludeWords'],
            self::ALLOW_TYPOS_ON_NUMERIC_TOKENS_PARAM => true,
            self::ALTERNATIVES_AS_EXACT_PARAM => ['ignorePlurals', 'singleWordSynonym'],
            self::ANALYTICS_PARAM => true,
            self::ANALYTICS_TAGS_PARAM => [],
            self::ATTRIBUTES_TO_HIGHLIGHT_PARAM => ['*'],
            self::ATTRIBUTES_TO_RETRIEVE_PARAM => ['*'],
            self::DECOMPOUND_QUERY_PARAM => true,
            self::DISABLE_EXACT_ON_ATTRIBUTES_PARAM => [],
            self::DISABLE_TYPO_TOLERANCE_ON_ATTRIBUTES_PARAM => [],
            self::ENABLE_AB_TEST_PARAM => true,
            self::ENABLE_PERSONALIZATION_PARAM => false,
            self::HIGHLIGHT_POST_TAG_PARAM => '</em>',
            self::HIGHLIGHT_PRE_TAG_PARAM => '<em>',
            self::MAX_VALUES_PER_FACET_PARAM => 100,
            self::QUERY_TYPE_PARAM => 'prefixLast',
            self::SORT_FACET_VALUES_BY_PARAM => 'count',
            self::SYNONYMS_PARAM => true,
        ]);

        $resolver->setAllowedTypes(self::ADVANCED_SYNTAX_PARAM, 'bool');
        $resolver->setAllowedTypes(self::ADVANCED_SYNTAX_FEATURES_PARAM, 'string[]');
        $resolver->setAllowedTypes(self::ALLOW_TYPOS_ON_NUMERIC_TOKENS_PARAM, 'bool');
        $resolver->setAllowedTypes(self::ALTERNATIVES_AS_EXACT_PARAM, 'string[]');
        $resolver->setAllowedTypes(self::ANALYTICS_PARAM, 'bool');
        $resolver->setAllowedTypes(self::ANALYTICS_TAGS_PARAM, 'string[]');
        $resolver->setAllowedTypes(self::ATTRIBUTES_TO_HIGHLIGHT_PARAM, 'string[]');
        $resolver->setAllowedTypes(self::ATTRIBUTES_TO_RETRIEVE_PARAM, 'string[]');
        $resolver->setAllowedTypes(self::DECOMPOUND_QUERY_PARAM, 'bool');
        $resolver->setAllowedTypes(self::DISABLE_EXACT_ON_ATTRIBUTES_PARAM, 'string[]');
        $resolver->setAllowedTypes(self::DISABLE_TYPO_TOLERANCE_ON_ATTRIBUTES_PARAM, 'string[]');
        $resolver->setAllowedTypes(self::ENABLE_AB_TEST_PARAM, 'bool');
        $resolver->setAllowedTypes(self::ENABLE_PERSONALIZATION_PARAM, 'bool');
        $resolver->setAllowedTypes(self::HIGHLIGHT_POST_TAG_PARAM, 'string');
        $resolver->setAllowedTypes(self::HIGHLIGHT_PRE_TAG_PARAM, 'string');
        $resolver->setAllowedTypes(self::MAX_VALUES_PER_FACET_PARAM, 'int');
        $resolver->setAllowedTypes(self::QUERY_TYPE_PARAM, 'string');
        $resolver->setAllowedTypes(self::SORT_FACET_VALUES_BY_PARAM, 'string');
        $resolver->setAllowedTypes(self::SYNONYMS_PARAM, 'bool');

        $resolver->setAllowedValues(self::ADVANCED_SYNTAX_FEATURES_PARAM, function (array $values) {
            foreach ($values as $value) {
                if (!\in_array($value, ['exactPhrase', 'excludeWords'])) {
                    return false;
                }
            }

            return true;
        });
        $resolver->setAllowedValues(self::ALTERNATIVES_AS_EXACT_PARAM, function (array $values) {
            foreach ($values as $value) {
                if (!\in_array($value, ['ignoreConjugations', 'ignorePlurals', 'multiWordsSynonym', 'singleWordSynonym'])) {
                    return false;
                }
            }

            return true;
        });
        $resolver->setAllowedValues(self::MAX_VALUES_PER_FACET_PARAM, fn (int $value): bool => $value <= 1000);
        $resolver->setAllowedValues(self::QUERY_TYPE_PARAM, ['prefixAll', 'prefixLast', 'prefixNone']);
        $resolver->setAllowedValues(self::SORT_FACET_VALUES_BY_PARAM, ['count', 'alpha']);
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
