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

namespace Mezcalito\UxSearchBundle\Context;

use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\ResultSet\ResultSet;
use Mezcalito\UxSearchBundle\Search\SearchInterface;

class Context
{
    private Query $query;

    private SearchInterface $search;

    private ?ResultSet $results = null;

    public function getQuery(): Query
    {
        return $this->query;
    }

    public function setQuery(Query $query): static
    {
        $this->query = $query;

        return $this;
    }

    public function getSearch(): SearchInterface
    {
        return $this->search;
    }

    public function setSearch(SearchInterface $search): static
    {
        $this->search = $search;

        return $this;
    }

    public function getResults(): ?ResultSet
    {
        return $this->results;
    }

    public function setResults(?ResultSet $results): static
    {
        $this->results = $results;

        return $this;
    }
}
