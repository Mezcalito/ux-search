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
use Mezcalito\UxSearchBundle\Exception\AdapterException;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

readonly class Searcher
{
    public function __construct(
        private AdapterProvider $adapterProvider,
        private ContextProvider $contextProvider,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function search(Query $query, SearchInterface $search): ResultSet\ResultSet
    {
        $this->sanitizeQuery($query, $search);

        $eventDispatcher = $search->getEventDispatcher();
        $search->addEventSubscriber(new ContextSubscriber($this->contextProvider));

        $eventDispatcher->dispatch(new PreSearchEvent($query, $search));

        $adapter = $this->adapterProvider->getAdapter($search->getAdapterName());

        $optionResolver = new OptionsResolver();
        $adapter->configureParameters($optionResolver);
        $search->setResolvedAdapterParameters($optionResolver->resolve($search->getAdapterParameters()));

        try {
            $results = $adapter->search($query, $search);
        } catch (\Throwable $exception) {
            $this->logger?->error('Search failed for index "{index}": {message}', [
                'index' => $search->getIndexName(),
                'message' => $exception->getMessage(),
                'exception' => $exception,
            ]);

            throw AdapterException::searchFailed($search->getIndexName(), $exception);
        }

        $eventDispatcher->dispatch(new PostSearchEvent($query, $search, $results));

        return $results;
    }

    /**
     * Client-writable query values (sort, hits per page, page, filter properties)
     * must be constrained to what the search declares before reaching adapters.
     */
    private function sanitizeQuery(Query $query, SearchInterface $search): void
    {
        $allowedSorts = array_map(static fn (Sort $sort) => $sort->getKey(), $search->getAvailableSorts());
        if (null !== $query->getActiveSort() && !\in_array($query->getActiveSort(), $allowedSorts, true)) {
            $query->setActiveSort([] !== $allowedSorts ? current($allowedSorts) : null);
        }

        $availableHitsPerPage = $search->getAvailableHitsPerPage();
        if ([] !== $availableHitsPerPage && !\in_array($query->getActiveHitsPerPage(), $availableHitsPerPage, true)) {
            $query->setActiveHitsPerPage((int) current($availableHitsPerPage));
        }

        if ($query->getCurrentPage() < 1) {
            $query->setCurrentPage(1);
        }

        $facetProperties = array_map(static fn (Facet $facet) => $facet->getProperty(), $search->getFacets());
        foreach ($query->getActiveFilters() as $filter) {
            if (!\in_array($filter->getProperty(), $facetProperties, true)) {
                $query->removeActiveFilter($filter);
            }
        }
    }
}
