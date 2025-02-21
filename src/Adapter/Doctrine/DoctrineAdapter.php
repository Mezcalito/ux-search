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
use Mezcalito\UxSearchBundle\Search\Filter\RangeFilter;
use Mezcalito\UxSearchBundle\Search\Filter\TermFilter;
use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\ResultSet\FacetStat;
use Mezcalito\UxSearchBundle\Search\ResultSet\FacetTermDistribution;
use Mezcalito\UxSearchBundle\Search\ResultSet\Hit;
use Mezcalito\UxSearchBundle\Search\ResultSet\ResultSet;
use Mezcalito\UxSearchBundle\Search\SearchInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

readonly class DoctrineAdapter implements AdapterInterface
{
    public const string MAX_FACET_VALUES_PARAM = 'maxFacetValues';

    public const string QUERY_BUILDER_ALIAS = 'queryBuilderAlias';

    public const string QUERY_BUILDER = 'queryBuilder';

    public const string SEARCH_FIELDS = 'searchFields';

    public function __construct(private EntityManagerInterface $manager)
    {
    }

    public function search(Query $query, SearchInterface $search): ResultSet
    {
        $helper = new QueryBuilderHelper($this->manager, $query, $search);

        $paginator = new Paginator($helper->getResultsQuery()->getQuery(), fetchJoinCollection: true);
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
            self::QUERY_BUILDER => function (QueryBuilder $queryBuilder) {},
            self::SEARCH_FIELDS => [],
        ]);

        $resolver->setAllowedTypes(self::MAX_FACET_VALUES_PARAM, 'int');
        $resolver->setAllowedTypes(self::QUERY_BUILDER_ALIAS, 'string');
        $resolver->setAllowedTypes(self::QUERY_BUILDER, 'Closure');
        $resolver->setAllowedTypes(self::SEARCH_FIELDS, 'string[]');
    }

    private function getFacetDistributions(Query $query, SearchInterface $search): array
    {
        $distributions = [];

        $helper = new QueryBuilderHelper($this->manager, $query, $search);

        foreach ($search->getFacets() as $facet) {
            $filter = $query->getActiveFilter($facet->getProperty());
            $checkedValues = [];
            if ($filter instanceof TermFilter) {
                $checkedValues = $filter->getValues();
            }

            $checkedFacets = [];
            $uncheckedFacets = [];
            $qb = $helper->getFacetTermQuery($facet);
            foreach ($qb->getQuery()->getArrayResult() as $row) {
                if (\in_array($row['value'], $checkedValues)) {
                    $checkedFacets[$row['value']] = $row['total'];
                } else {
                    $uncheckedFacets[$row['value']] = $row['total'];
                }
            }

            $values = $checkedFacets + $uncheckedFacets;

            $distributions[] = (new FacetTermDistribution())
                ->setProperty($facet->getProperty())
                ->setValues($values)
                ->setCheckedValues($checkedValues);
        }

        return $distributions;
    }

    private function getFacetStats(Query $query, SearchInterface $search): array
    {
        $stats = [];

        $helper = new QueryBuilderHelper($this->manager, $query, $search);

        foreach ($search->getFacets() as $facet) {
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

            $stats[] = (new FacetStat(
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
