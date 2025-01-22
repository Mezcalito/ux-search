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
    public function getDistribution(): FacetTermDistribution
    {
        return $this->contextProvider->getCurrentContext()->getResults()->getFacetDistribution($this->property);
    }
}
