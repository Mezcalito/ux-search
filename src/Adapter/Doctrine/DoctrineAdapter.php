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

namespace Mezcalito\UxSearchBundle\Adapter\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Mezcalito\UxSearchBundle\Adapter\AdapterInterface;
use Mezcalito\UxSearchBundle\Search\Facet;
use Mezcalito\UxSearchBundle\Search\Filter\RangeFilter;
use Mezcalito\UxSearchBundle\Search\Filter\TermFilter;
use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\ResultSet\FacetStat;
use Mezcalito\UxSearchBundle\Search\ResultSet\FacetTermDistribution;
use Mezcalito\UxSearchBundle\Search\ResultSet\Hit;
use Mezcalito\UxSearchBundle\Search\ResultSet\ResultSet;
use Mezcalito\UxSearchBundle\Search\SearchInterface;
use Mezcalito\UxSearchBundle\Twig\Components\Facet\AbstractFacet;
use Symfony\Component\OptionsResolver\OptionsResolver;

readonly class DoctrineAdapter implements AdapterInterface
{
    public const string MAX_FACET_VALUES_PARAM = 'maxFacetValues';

    public const string QUERY_BUILDER_ALIAS = 'queryBuilderAlias';

    public const string QUERY_BUILDER = 'queryBuilder';

    public const string SEARCH_FIELDS = 'searchFields';

    /**
     * When true (default), total and facet counts use count(DISTINCT <identifier>),
     * which is required for correct counts when a facet or filter introduces a
     * to-many join (row fan-out). When the facets/filters are plain columns on the
     * base entity, the DISTINCT is redundant against the primary key and forces a
     * full sort of the table on every count query; set this to false to emit a
     * plain count(<identifier>) and avoid that cost. See mezcalito/ux-search#46.
     */
    public const string COUNT_DISTINCT = 'countDistinct';

    /**
     * When true (default), the results paginator runs in fetch-join-collection mode,
     * which wraps the query in a DISTINCT id / ROW_NUMBER() output walker. That is
     * required only when the query fetch-joins a to-many collection; for a plain
     * single-table search it forces a far more expensive paginated query. Set this to
     * false when the search does not fetch-join any collection to use a simple
     * LIMIT/OFFSET instead. See mezcalito/ux-search#46.
     */
    public const string FETCH_JOIN_COLLECTION = 'fetchJoinCollection';

    public function __construct(private EntityManagerInterface $manager)
    {
    }

    public function search(Query $query, SearchInterface $search): ResultSet
    {
        $helper = new QueryBuilderHelper($this->manager, $query, $search);

        $paginator = new Paginator(
            $helper->getResultsQuery()->getQuery(),
            fetchJoinCollection: $search->getResolvedAdapterParameter(self::FETCH_JOIN_COLLECTION),
        );
        $hits = [];
        foreach ($paginator as $item) {
            $hits[] = new Hit($item, 1);
        }

        $total = $helper->getTotalResultsQuery()->getQuery()->getSingleScalarResult();

        return (new ResultSet())
            ->setIndexUid($search->getIndexName())
            ->setHits($hits)
            ->setFacetDistributions($this->getFacetDistributions($query, $search))
            ->setFacetStats($this->getFacetStats($query, $search))
            ->setTotalResults($total);
    }

    public function configureParameters(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            self::MAX_FACET_VALUES_PARAM => 100,
            self::QUERY_BUILDER_ALIAS => 'o',
            self::QUERY_BUILDER => static function (QueryBuilder $queryBuilder) {},
            self::SEARCH_FIELDS => [],
            self::COUNT_DISTINCT => true,
            self::FETCH_JOIN_COLLECTION => true,
        ]);

        $resolver->setAllowedTypes(self::MAX_FACET_VALUES_PARAM, 'int');
        $resolver->setAllowedTypes(self::QUERY_BUILDER_ALIAS, 'string');
        $resolver->setAllowedTypes(self::QUERY_BUILDER, 'Closure');
        $resolver->setAllowedTypes(self::SEARCH_FIELDS, 'string[]');
        $resolver->setAllowedTypes(self::COUNT_DISTINCT, 'bool');
        $resolver->setAllowedTypes(self::FETCH_JOIN_COLLECTION, 'bool');
    }

    /**
     * @return array<string, FacetTermDistribution>
     */
    private function getFacetDistributions(Query $query, SearchInterface $search): array
    {
        $distributions = [];

        $helper = new QueryBuilderHelper($this->manager, $query, $search);

        foreach ($search->getFacets() as $facet) {
            // Range facets render from min/max stats only; skip the term-distribution
            // query they never consume (it can be a huge GROUP BY on a high-cardinality
            // numeric column). See mezcalito/ux-search#46.
            if ($this->facetUsesStats($facet)) {
                continue;
            }

            $filter = $query->getActiveFilter($facet->getProperty());
            $checkedValues = [];
            if ($filter instanceof TermFilter) {
                $checkedValues = $filter->getValues();
            }

            $checkedFacets = [];
            $uncheckedFacets = [];
            $qb = $helper->getFacetTermQuery($facet);
            foreach ($qb->getQuery()->getArrayResult() as $row) {
                $rowValue = $row['value'] instanceof \BackedEnum ? $row['value']->value : $row['value'];

                if (\in_array($rowValue, $checkedValues)) {
                    $checkedFacets[$rowValue] = $row['total'];
                } else {
                    $uncheckedFacets[$rowValue] = $row['total'];
                }
            }

            $values = $checkedFacets + $uncheckedFacets;

            $distributions[$facet->getProperty()] = (new FacetTermDistribution())
                ->setProperty($facet->getProperty())
                ->setValues($values)
                ->setCheckedValues($checkedValues);
        }

        return $distributions;
    }

    /**
     * Whether a facet renders from min/max stats (a range facet) rather than a term
     * distribution, based on its display component. Facets with no component (or a
     * non-range component) default to a term distribution.
     */
    private function facetUsesStats(Facet $facet): bool
    {
        $component = $facet->getDisplayComponent();

        return null !== $component
            && is_subclass_of($component, AbstractFacet::class)
            && $component::usesFacetStats();
    }

    /**
     * @return array<string, FacetStat>
     */
    private function getFacetStats(Query $query, SearchInterface $search): array
    {
        $stats = [];

        $helper = new QueryBuilderHelper($this->manager, $query, $search);

        foreach ($search->getFacets() as $facet) {
            // Only range facets consume stats; list facets never do, so skip the
            // min/max query for them (it is a full scan, e.g. min()/max() on a text
            // column). See mezcalito/ux-search#46.
            if (!$this->facetUsesStats($facet)) {
                continue;
            }

            $filter = $query->getActiveFilter($facet->getProperty());

            $userMin = null;
            $userMax = null;
            if ($filter instanceof RangeFilter) {
                $userMin = $filter->getMin();
                $userMax = $filter->getMax();
            }

            $qb = $helper->getFacetStatsQuery($facet);

            $rs = $qb->getQuery()->getArrayResult()[0];

            if (\is_string($rs['min']) || \is_string($rs['max'])) {
                continue;
            }

            $stats[$facet->getProperty()] = (new FacetStat(
                property: $facet->getProperty(),
                min: $rs['min'] ?? 0,
                max: $rs['max'] ?? 0,
                userMin: $userMin,
                userMax: $userMax
            ));
        }

        return $stats;
    }
}
