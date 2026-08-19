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

use Mezcalito\UxSearchBundle\Exception\ContextException;
use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\SearchInterface;
use Symfony\Contracts\Service\ResetInterface;

class ContextProvider implements ResetInterface
{
    private ?Context $context = null;

    public function init(Query $query, SearchInterface $search): void
    {
        $this->context = (new Context())
            ->setQuery($query)
            ->setSearch($search)
        ;
    }

    public function hasCurrentContext(): bool
    {
        return $this->context instanceof Context;
    }

    public function getCurrentContext(): Context
    {
        if (!$this->hasCurrentContext()) {
            throw ContextException::contextNotInitialized();
        }

        return $this->context;
    }

    public function reset(): void
    {
        $this->context = null;
    }
}
