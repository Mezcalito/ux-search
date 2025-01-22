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

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

interface SearchInterface
{
    public function create(array $options = []): static;

    public function build(array $options = []): void;

    public function getIndexName(): ?string;

    public function getAdapterName(): ?string;

    public function getAvailableHitsPerPage(): array;

    public function setAvailableHitsPerPage(array $availableHitsPerPage): static;

    public function addAvailableSort(?string $key, string $label): static;

    public function getAvailableSorts(): array;

    public function addFacet(string $property, string $label, ?string $displayComponent = null, array $props = []): static;

    /**
     * @return Facet[]
     */
    public function getFacets(): array;

    public function getFacet(string $property): ?Facet;

    public function getEventDispatcher(): EventDispatcher;

    public function addEventSubscriber(EventSubscriberInterface $eventSubscriber): static;

    public function addEventListener(string $eventName, callable $listener, int $priority = 0): static;

    public function getAdapterParameters(): array;

    public function setAdapterParameters(array $adapterParameters): static;

    public function getResolvedAdapterParameters(): array;

    public function getResolvedAdapterParameter(string $name): mixed;

    public function setResolvedAdapterParameters(array $resolvedAdapterParameters): static;

    public function createQuery(): Query;

    public function hasUrlRewriting(): bool;

    public function enableUrlRewriting(): static;

    public function getUrlFormater(): string;

    public function setUrlFormater(string $urlFormater): static;
}
