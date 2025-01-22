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

use Mezcalito\UxSearchBundle\Search\ResultSet\FacetStat;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

class RangeInput extends AbstractFacet
{
    #[ExposeInTemplate]
    public function getFacetStat(): FacetStat
    {
        return $this->contextProvider->getCurrentContext()->getResults()->getFacetStat($this->property);
    }
}
