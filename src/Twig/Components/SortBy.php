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
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

class SortBy
{
    public function __construct(
        private readonly ContextProvider $contextProvider,
    ) {
    }

    #[ExposeInTemplate]
    public function getAvailableSorts(): array
    {
        return $this->contextProvider->getCurrentContext()->getSearch()->getAvailableSorts();
    }

    #[ExposeInTemplate]
    public function getActiveSort(): ?string
    {
        return $this->contextProvider->getCurrentContext()->getQuery()->getActiveSort();
    }
}
