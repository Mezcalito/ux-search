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

namespace Mezcalito\UxSearchBundle\Twig\Components\Facet;

use Mezcalito\UxSearchBundle\Search\ResultSet\FacetTermDistribution;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

class RefinementList extends AbstractFacet
{
    public int $limit = 10;

    #[ExposeInTemplate]
    public bool $enableSearch = false;

    #[ExposeInTemplate]
    public string $searchPlaceholder = '';

    public string $sortBy = 'count';

    #[ExposeInTemplate]
    public bool $enableSort = false;

    #[ExposeInTemplate]
    public function getSortBy(): string
    {
        $query = $this->contextProvider->getCurrentContext()->getQuery();

        return $query->getFacetSortPreference($this->property) ?? $this->sortBy;
    }

    #[ExposeInTemplate]
    public function getDistribution(): FacetTermDistribution
    {
        $distribution = $this->contextProvider->getCurrentContext()->getResults()->getFacetDistribution($this->property);
        $sortBy = $this->getSortBy();
        $values = $distribution->getValues();

        // Sort the values based on user preference
        if ('name' === $sortBy) {
            // Sort alphabetically by key (facet value name)
            ksort($values, \SORT_NATURAL | \SORT_FLAG_CASE);
        } else {
            // Sort by count (value), highest first
            arsort($values, \SORT_NUMERIC);
        }

        $distribution->setValues($values);

        return $distribution;
    }
}
