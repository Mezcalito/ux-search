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

namespace Mezcalito\UxSearchBundle\Search\ResultSet;

use Mezcalito\UxSearchBundle\Exception\ResultSetException;

class ResultSet
{
    private ?string $indexUid = null;

    /** @var Hit[] */
    private array $hits = [];

    private int $totalResults = 0;

    /** @var array<string, FacetTermDistribution> */
    private array $facetDistributions = [];

    /** @var array<string, FacetStat> */
    private array $facetStats = [];

    public function getIndexUid(): ?string
    {
        return $this->indexUid;
    }

    public function setIndexUid(?string $indexUid): static
    {
        $this->indexUid = $indexUid;

        return $this;
    }

    public function getHits(): array
    {
        return $this->hits;
    }

    public function setHits(array $hits): static
    {
        $this->hits = $hits;

        return $this;
    }

    public function getTotalResults(): int
    {
        return $this->totalResults;
    }

    public function setTotalResults(int $totalResults): static
    {
        $this->totalResults = $totalResults;

        return $this;
    }

    public function getFacetDistributions(): array
    {
        return $this->facetDistributions;
    }

    public function setFacetDistributions(array $facetDistributions): static
    {
        $this->facetDistributions = [];
        foreach ($facetDistributions as $facetDistribution) {
            $this->facetDistributions[$facetDistribution->getProperty()] = $facetDistribution;
        }

        return $this;
    }

    public function getFacetDistribution(string $property): FacetTermDistribution
    {
        if (!isset($this->facetDistributions[$property])) {
            throw ResultSetException::facetDistributionNotFound($property);
        }

        return $this->facetDistributions[$property];
    }

    public function getFacetStats(): array
    {
        return $this->facetStats;
    }

    public function setFacetStats(array $facetStats): static
    {
        $this->facetStats = [];
        foreach ($facetStats as $facetStat) {
            $this->facetStats[$facetStat->getProperty()] = $facetStat;
        }

        return $this;
    }

    public function getFacetStat(string $property): FacetStat
    {
        if (!isset($this->facetStats[$property])) {
            throw ResultSetException::facetStatNotFound($property);
        }

        return $this->facetStats[$property];
    }
}
