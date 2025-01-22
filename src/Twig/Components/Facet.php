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

namespace Mezcalito\UxSearchBundle\Twig\Components;

use Mezcalito\UxSearchBundle\Context\ContextProvider;
use Mezcalito\UxSearchBundle\Twig\Components\Facet\RefinementList;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

class Facet
{
    public string $property = '';

    public function __construct(
        private readonly ContextProvider $contextProvider,
    ) {
    }

    #[ExposeInTemplate]
    public function getComponent(): string
    {
        $facet = $this->contextProvider->getCurrentContext()->getSearch()->getFacet($this->property);

        return $facet->getDisplayComponent() ?? RefinementList::class;
    }
}
