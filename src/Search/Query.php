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

namespace Mezcalito\UxSearchBundle\Search;

use Mezcalito\UxSearchBundle\Search\Filter\FilterInterface;

class Query
{
    private string $queryString = '';

    /** @var array<string, FilterInterface> */
    private array $activeFilters = [];

    private int $currentPage = 1;

    private ?string $activeSort = null;

    private int $activeHitsPerPage = 12;

    public function getQueryString(): string
    {
        return $this->queryString;
    }

    public function setQueryString(string $queryString): static
    {
        $this->queryString = $queryString;

        return $this;
    }

    /**
     * @return array<string, FilterInterface>
     */
    public function getActiveFilters(): array
    {
        return $this->activeFilters;
    }

    /**
     * @param FilterInterface[] $activeFilters
     */
    public function setActiveFilters(array $activeFilters): static
    {
        $this->activeFilters = [];
        foreach ($activeFilters as $filter) {
            $this->activeFilters[$filter->getProperty()] = $filter;
        }

        return $this;
    }

    public function getActiveFilter(string $property): ?FilterInterface
    {
        return $this->activeFilters[$property] ?? null;
    }

    public function addActiveFilter(FilterInterface $filter): static
    {
        $this->activeFilters[$filter->getProperty()] = $filter;

        return $this;
    }

    public function hasActiveFilter(string $property): bool
    {
        return isset($this->activeFilters[$property]);
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function setCurrentPage(int $currentPage): static
    {
        $this->currentPage = $currentPage;

        return $this;
    }

    public function setActiveSort(?string $sort): static
    {
        $this->activeSort = $sort;

        return $this;
    }

    public function getActiveSort(): ?string
    {
        return $this->activeSort;
    }

    public function getActiveHitsPerPage(): int
    {
        return $this->activeHitsPerPage;
    }

    public function setActiveHitsPerPage(int $activeHitsPerPage): static
    {
        $this->activeHitsPerPage = $activeHitsPerPage;

        return $this;
    }

    public function removeActiveFilter(FilterInterface $filter): void
    {
        unset($this->activeFilters[$filter->getProperty()]);
    }
}
