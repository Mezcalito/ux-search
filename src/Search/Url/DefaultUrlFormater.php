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

namespace Mezcalito\UxSearchBundle\Search\Url;

use Mezcalito\UxSearchBundle\Search\Filter\RangeFilter;
use Mezcalito\UxSearchBundle\Search\Filter\TermFilter;
use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\SearchInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DefaultUrlFormater implements UrlFormaterInterface
{
    private const string QUERY = 'query';

    private const string PAGE = 'page';

    private const string SORT_BY = 'sortBy';

    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function generateUrl(CurrentRequest $currentRequest, SearchInterface $search, Query $query): string
    {
        $params = $this->clearParameters($currentRequest->parameters, $search);

        if ('' !== $query->getQueryString()) {
            $params[self::QUERY] = $query->getQueryString();
        }

        if ($query->getActiveSort()) {
            $params[self::SORT_BY] = $query->getActiveSort();
        }

        if ($query->getCurrentPage() > 1) {
            $params[self::PAGE] = $query->getCurrentPage();
        }

        foreach ($query->getActiveFilters() as $filter) {
            $propertyForUrl = str_replace('.', '_', $filter->getProperty());
            if ($filter instanceof TermFilter) {
                $params[$propertyForUrl] = implode('~~', $filter->getValues());
            }

            if ($filter instanceof RangeFilter) {
                $params[$propertyForUrl.'Min'] = $filter->getMin();
                $params[$propertyForUrl.'Max'] = $filter->getMax();
            }
        }

        return $this->urlGenerator->generate($currentRequest->route, $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function applyFilters(CurrentRequest $currentRequest, SearchInterface $search, Query $query): void
    {
        if ($q = $currentRequest->parameters[self::QUERY] ?? null) {
            $query->setQueryString($q);
        }

        if ($s = $currentRequest->parameters[self::SORT_BY] ?? null) {
            $query->setActiveSort($s);
        }

        if ($p = $currentRequest->parameters[self::PAGE] ?? null) {
            $query->setCurrentPage((int) $p);
        }

        foreach ($search->getFacets() as $facet) {
            $property = $facet->getProperty();
            $propertyInUrl = str_replace('.', '_', $property);

            if ($value = $currentRequest->parameters[$propertyInUrl] ?? null) {
                $query->addActiveFilter(new TermFilter($property, explode('~~', (string) $value)));
            }

            $minValue = $currentRequest->parameters[$propertyInUrl.'Min'] ?? null;
            $maxValue = $currentRequest->parameters[$propertyInUrl.'Max'] ?? null;
            if ($minValue || $maxValue) {
                $query->addActiveFilter(new RangeFilter(
                    $property,
                    null !== $minValue ? (float) $minValue : null,
                    null !== $maxValue ? (float) $maxValue : null
                ));
            }
        }
    }

    private function clearParameters(array $params, SearchInterface $search): array
    {
        $searchableParameterKeys = $this->getSearchableParameterKeys($search);

        return array_filter($params, fn ($key) => !\in_array($key, $searchableParameterKeys), \ARRAY_FILTER_USE_KEY);
    }

    private function getSearchableParameterKeys(SearchInterface $search): array
    {
        $keys = [self::PAGE, self::SORT_BY];
        foreach ($search->getFacets() as $facet) {
            $propertyInUrl = str_replace('.', '_', $facet->getProperty());
            $keys[] = $propertyInUrl;
            $keys[] = $propertyInUrl.'Min';
            $keys[] = $propertyInUrl.'Max';
        }

        return $keys;
    }
}
