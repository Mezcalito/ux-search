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
use Mezcalito\UxSearchBundle\Adapter\AbstractAdapter;
use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\ResultSet\Hit;
use Mezcalito\UxSearchBundle\Search\ResultSet\ResultSet;
use Mezcalito\UxSearchBundle\Search\SearchInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeilisearchAdapter extends AbstractAdapter
{
    public const string ATTRIBUTES_TO_RETRIEVE_PARAM = 'attributesToRetrieve';

    public const string ATTRIBUTES_TO_CROP_PARAM = 'attributesToCrop';

    public const string CROP_LENGTH_PARAM = 'cropLength';

    public const string CROP_MARKER_PARAM = 'cropMarker';

    public const string ATTRIBUTES_TO_HIGHLIGHT_PARAM = 'attributesToHighlight';

    public const string HIGHLIGHT_PRE_TAG_PARAM = 'highlightPreTag';

    public const string HIGHLIGHT_POST_TAG_PARAM = 'highlightPostTag';

    public function __construct(
        private readonly Client $client,
        private readonly QueryBuilder $queryBuilder,
    ) {
    }

    public function getFacetDistributionKey(): string
    {
        return 'facetDistribution';
    }

    public function getFacetStatsKey(): string
    {
        return 'facetStats';
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

        [$facetsDistributions, $facetStats] = $this->getFacets($results, $search, $query);

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
}
