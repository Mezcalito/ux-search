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
use Mezcalito\UxSearchBundle\Search\Facet;
use Mezcalito\UxSearchBundle\Search\Filter\FilterInterface;
use Mezcalito\UxSearchBundle\Search\Filter\RangeFilter;
use Mezcalito\UxSearchBundle\Search\Filter\TermFilter;
use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\SearchInterface;

use function Symfony\Component\String\u;

readonly class QueryBuilderHelper
{
    public function __construct(
        private EntityManagerInterface $manager,
        private Query $query,
        private SearchInterface $search,
    ) {
    }

    public function getTotalResultsQuery(): QueryBuilder
    {
        $qb = $this->createBaseQueryBuilder()
            ->select(\sprintf('count(DISTINCT (%s)) AS total', $this->getIdentifierField()));

        $this->applyQueryString($qb);

        foreach ($this->query->getActiveFilters() as $filter) {
            $this->applyFilter($qb, $filter);
        }

        return $qb;
    }

    public function getResultsQuery(): QueryBuilder
    {
        $qb = $this->createBaseQueryBuilder();
        $this->applyPagination($qb);
        $this->applySort($qb);
        $this->applyQueryString($qb);

        foreach ($this->query->getActiveFilters() as $filter) {
            $this->applyFilter($qb, $filter);
        }

        return $qb;
    }

    public function getFacetTermQuery(Facet $facet): QueryBuilder
    {
        $qb = $this->createBaseQueryBuilder();
        $qb
            ->select(\sprintf('%s as value, count(%s) AS total', $facet->getProperty(), $facet->getProperty()))
            ->orderBy('total', 'desc')
            ->groupBy($facet->getProperty())
            ->setMaxResults($this->search->getResolvedAdapterParameter(DoctrineAdapter::MAX_FACET_VALUES_PARAM));

        $this->applyQueryString($qb);

        foreach ($this->query->getActiveFilters() as $filter) {
            if ($filter->getProperty() === $facet->getProperty()) {
                continue;
            }

            $this->applyFilter($qb, $filter);
        }

        return $qb;
    }

    public function getFacetStatsQuery(mixed $facet): QueryBuilder
    {
        $qb = $this->createBaseQueryBuilder();
        $qb
            ->select(\sprintf('min(%s) as min, max(%s) AS max', $facet->getProperty(), $facet->getProperty()));

        $this->applyQueryString($qb);

        foreach ($this->query->getActiveFilters() as $filter) {
            if ($filter->getProperty() === $facet->getProperty()) {
                continue;
            }

            $this->applyFilter($qb, $filter);
        }

        return $qb;
    }

    private function createBaseQueryBuilder(): QueryBuilder
    {
        $qb = $this->manager
            ->getRepository($this->search->getIndexName())
            ->createQueryBuilder($this->search->getResolvedAdapterParameter(DoctrineAdapter::QUERY_BUILDER_ALIAS));

        $this->search->getResolvedAdapterParameter(DoctrineAdapter::QUERY_BUILDER)($qb);

        return $qb;
    }

    private function getIdentifierField(): string
    {
        $metadata = $this->manager->getClassMetadata($this->search->getIndexName());

        return \sprintf('%s.%s',
            $this->search->getResolvedAdapterParameter(DoctrineAdapter::QUERY_BUILDER_ALIAS),
            $metadata->getIdentifier()[0]
        );
    }

    private function applyFilter(QueryBuilder $qb, FilterInterface $filter)
    {
        if ($filter instanceof TermFilter && $filter->hasValues()) {
            $parameterName = u(\sprintf('%s_terms', $filter->getProperty()))->snake()->toString();

            $qb->andWhere(\sprintf('%s in (:%s)', $filter->getProperty(), $parameterName));
            $qb->setParameter($parameterName, array_values($filter->getValues()));
        }

        if ($filter instanceof RangeFilter && $filter->getMax()) {
            $parameterName = u(\sprintf('%s_max', $filter->getProperty()))->snake()->toString();
            $qb->andWhere(\sprintf('%s <= :%s ', $filter->getProperty(), $parameterName));
            $qb->setParameter($parameterName, $filter->getMax());
        }

        if ($filter instanceof RangeFilter && $filter->getMin()) {
            $parameterName = u(\sprintf('%s_min', $filter->getProperty()))->snake()->toString();
            $qb->andWhere(\sprintf('%s >= :%s', $filter->getProperty(), $parameterName));
            $qb->setParameter($parameterName, $filter->getMin());
        }
    }

    private function applyPagination(QueryBuilder $qb): void
    {
        $qb
            ->setFirstResult($this->query->getActiveHitsPerPage() * ($this->query->getCurrentPage() - 1))
            ->setMaxResults($this->query->getActiveHitsPerPage());
    }

    private function applySort(QueryBuilder $qb): void
    {
        if ($this->query->getActiveSort()) {
            [$sort, $order] = explode(':', $this->query->getActiveSort());
            $qb->orderBy($sort, $order);
        }
    }

    private function applyQueryString(QueryBuilder $qb)
    {
        $fields = $this->search->getResolvedAdapterParameter(DoctrineAdapter::SEARCH_FIELDS);
        if ('' === $this->query->getQueryString() || 0 === \count($fields)) {
            return;
        }

        $orX = $qb->expr()->orX();
        foreach ($fields as $fieldName) {
            $orX->add(\sprintf('%s like :queryString', $fieldName));
        }

        $qb->add('where', $orX);

        $qb->setParameter('queryString', \sprintf('%%%s%%', $this->query->getQueryString()));
    }
}
