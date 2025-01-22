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

use Mezcalito\UxSearchBundle\Context\ContextProvider;
use Mezcalito\UxSearchBundle\Search\Facet;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use Symfony\UX\TwigComponent\Attribute\PreMount;

abstract class AbstractFacet
{
    public string $property;

    public function __construct(protected ContextProvider $contextProvider)
    {
    }

    #[ExposeInTemplate]
    public function getLabel(): string
    {
        return $this->getFacet()->getLabel();
    }

    #[ExposeInTemplate]
    protected function getFacet(): Facet
    {
        return $this->contextProvider->getCurrentContext()->getSearch()->getFacet($this->property);
    }

    #[PreMount(priority: -100)]
    public function mergeFacetData(array $data): array
    {
        $facet = $this->contextProvider->getCurrentContext()->getSearch()->getFacet($data['property']);

        return array_merge($facet->getProps(), $data);
    }
}
