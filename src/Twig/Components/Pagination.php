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

class Pagination
{
    public int $range = 2;

    public function __construct(
        private readonly ContextProvider $contextProvider,
    ) {
    }

    #[ExposeInTemplate]
    public function getStartRange(): int
    {
        return max($this->contextProvider->getCurrentContext()->getQuery()->getCurrentPage() - $this->range, 1);
    }

    #[ExposeInTemplate]
    public function getEndRange(): int
    {
        $endRange = min($this->contextProvider->getCurrentContext()->getQuery()->getCurrentPage() + $this->range, $this->getTotalPage() - $this->range);

        return 0 == $endRange ? $this->getTotalPage() : $endRange;
    }

    #[ExposeInTemplate]
    public function getTotalPage(): int
    {
        return (int) ceil($this->contextProvider->getCurrentContext()->getResults()->getTotalResults() / $this->contextProvider->getCurrentContext()->getQuery()->getActiveHitsPerPage());
    }

    #[ExposeInTemplate]
    public function getPage(): int
    {
        return $this->contextProvider->getCurrentContext()->getQuery()->getCurrentPage();
    }
}
