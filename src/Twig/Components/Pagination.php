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
use Mezcalito\UxSearchBundle\Search\ResultSet\ResultSet;
use Mezcalito\UxSearchBundle\Search\Url\UrlFormaterProvider;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

class Pagination
{
    public int $range = 2;

    public function __construct(
        private readonly ContextProvider $contextProvider,
        private readonly UrlFormaterProvider $urlFormaterProvider,
    ) {
    }

    public function getPageUrl(int $page): string
    {
        $context = $this->contextProvider->getCurrentContext();
        $currentRequest = $context->getCurrentRequest();

        if (!$currentRequest instanceof \Mezcalito\UxSearchBundle\Search\Url\CurrentRequest || !$context->getSearch()->hasUrlRewriting()) {
            return '?page='.$page;
        }

        $query = clone $context->getQuery();
        $query->setCurrentPage($page);

        return $this->urlFormaterProvider
            ->getUrlFormater($context->getSearch()->getUrlFormater())
            ->generateUrl($currentRequest, $context->getSearch(), $query);
    }

    #[ExposeInTemplate]
    public function getStartRange(): int
    {
        return max($this->contextProvider->getCurrentContext()->getQuery()->getCurrentPage() - $this->range, 1);
    }

    #[ExposeInTemplate]
    public function getEndRange(): int
    {
        return min($this->contextProvider->getCurrentContext()->getQuery()->getCurrentPage() + $this->range, $this->getTotalPage());
    }

    /**
     * Pages to render, where null marks an ellipsis.
     *
     * @return list<int|null>
     */
    #[ExposeInTemplate]
    public function getPages(): array
    {
        $total = $this->getTotalPage();
        $page = min(max($this->getPage(), 1), max($total, 1));

        $start = max(1, $page - $this->range);
        $end = min($total, $page + $this->range);

        $pages = [];

        if ($start > 1) {
            $pages[] = 1;
            if (3 === $start) {
                $pages[] = 2;
            } elseif ($start > 3) {
                $pages[] = null;
            }
        }

        foreach (range($start, $end) as $i) {
            $pages[] = $i;
        }

        if ($end < $total) {
            if ($end === $total - 2) {
                $pages[] = $total - 1;
            } elseif ($end < $total - 2) {
                $pages[] = null;
            }

            $pages[] = $total;
        }

        return $pages;
    }

    #[ExposeInTemplate]
    public function getTotalPage(): int
    {
        $results = $this->contextProvider->getCurrentContext()->getResults();
        if (!$results instanceof ResultSet) {
            return 0;
        }

        return (int) ceil($results->getTotalResults() / $this->contextProvider->getCurrentContext()->getQuery()->getActiveHitsPerPage());
    }

    #[ExposeInTemplate]
    public function getPage(): int
    {
        return $this->contextProvider->getCurrentContext()->getQuery()->getCurrentPage();
    }
}
