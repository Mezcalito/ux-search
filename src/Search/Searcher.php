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

use Mezcalito\UxSearchBundle\Adapter\AdapterProvider;
use Mezcalito\UxSearchBundle\Context\ContextProvider;
use Mezcalito\UxSearchBundle\Event\PostSearchEvent;
use Mezcalito\UxSearchBundle\Event\PreSearchEvent;
use Mezcalito\UxSearchBundle\EventSubscriber\ContextSubscriber;
use Symfony\Component\OptionsResolver\OptionsResolver;

readonly class Searcher
{
    public function __construct(
        private AdapterProvider $adapterProvider,
        private ContextProvider $contextProvider,
    ) {
    }

    public function search(Query $query, SearchInterface $search): ResultSet\ResultSet
    {
        $eventDispatcher = $search->getEventDispatcher();
        $search->addEventSubscriber(new ContextSubscriber($this->contextProvider));

        $eventDispatcher->dispatch(new PreSearchEvent($query, $search));

        $adapter = $this->adapterProvider->getAdapter($search->getAdapterName());

        $optionResolver = new OptionsResolver();
        $adapter->configureParameters($optionResolver);
        $search->setResolvedAdapterParameters($optionResolver->resolve($search->getAdapterParameters()));

        $results = $adapter->search($query, $search);

        $eventDispatcher->dispatch(new PostSearchEvent($query, $search, $results));

        return $results;
    }
}
