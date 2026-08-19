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

use Mezcalito\UxSearchBundle\Attribute\AsSearch;
use Mezcalito\UxSearchBundle\Exception\SearchException;
use Mezcalito\UxSearchBundle\Search\Url\DefaultUrlFormater;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Service\ResetInterface;

abstract class AbstractSearch implements SearchInterface, ResetInterface
{
    /** @var int[] */
    private array $availableHitsPerPage = [12];

    /** @var Sort[] */
    private array $availableSorts = [];

    /** @var Facet[] */
    private array $facets = [];

    private ?EventDispatcher $eventDispatcher = null;

    /** @var array<string, mixed> */
    private array $adapterParameters = [];

    /** @var array<string, mixed> */
    private array $resolvedAdapterParameters = [];

    private bool $urlRewriting = false;

    private ?string $urlFormater = null;

    /**
     * @param array<string, mixed> $options
     */
    public function create(array $options = []): static
    {
        $this->reset();
        $this->eventDispatcher = new EventDispatcher();
        $this->build($options);

        return $this;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function build(array $options = []): void
    {
    }

    public function getIndexName(): ?string
    {
        if ($attribute = (new \ReflectionClass(static::class))->getAttributes(AsSearch::class)) {
            return $attribute[0]->newInstance()->index;
        }

        return null;
    }

    public function getAdapterName(): ?string
    {
        if ($attribute = (new \ReflectionClass(static::class))->getAttributes(AsSearch::class)) {
            return $attribute[0]->newInstance()->adapter;
        }

        return null;
    }

    /**
     * @return int[]
     */
    public function getAvailableHitsPerPage(): array
    {
        return $this->availableHitsPerPage;
    }

    /**
     * @param int[] $availableHitsPerPage
     */
    public function setAvailableHitsPerPage(array $availableHitsPerPage): static
    {
        $this->availableHitsPerPage = $availableHitsPerPage;

        return $this;
    }

    public function addAvailableSort(?string $key, string $label): static
    {
        $this->availableSorts[] = new Sort($key, $label);

        return $this;
    }

    /**
     * @return Sort[]
     */
    public function getAvailableSorts(): array
    {
        return $this->availableSorts;
    }

    /**
     * @param array<string, mixed> $props
     */
    public function addFacet(string $property, string $label, ?string $displayComponent = null, array $props = []): static
    {
        $this->facets[] = (new Facet($property, $label, $displayComponent, $props));

        return $this;
    }

    /**
     * @return Facet[]
     */
    public function getFacets(): array
    {
        return $this->facets;
    }

    public function getFacet(string $property): ?Facet
    {
        foreach ($this->getFacets() as $facet) {
            if ($facet->getProperty() === $property) {
                return $facet;
            }
        }

        throw SearchException::facetNotConfigured($property);
    }

    public function getEventDispatcher(): EventDispatcher
    {
        return $this->eventDispatcher;
    }

    public function addEventSubscriber(EventSubscriberInterface $eventSubscriber): static
    {
        $this->eventDispatcher->addSubscriber($eventSubscriber);

        return $this;
    }

    public function addEventListener(string $eventName, callable $listener, int $priority = 0): static
    {
        $this->eventDispatcher->addListener($eventName, $listener, $priority);

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getAdapterParameters(): array
    {
        return $this->adapterParameters;
    }

    /**
     * @param array<string, mixed> $adapterParameters
     */
    public function setAdapterParameters(array $adapterParameters): static
    {
        $this->adapterParameters = $adapterParameters;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getResolvedAdapterParameters(): array
    {
        return $this->resolvedAdapterParameters;
    }

    public function getResolvedAdapterParameter(string $name): mixed
    {
        return $this->resolvedAdapterParameters[$name] ?? null;
    }

    /**
     * @param array<string, mixed> $resolvedAdapterParameters
     */
    public function setResolvedAdapterParameters(array $resolvedAdapterParameters): static
    {
        $this->resolvedAdapterParameters = $resolvedAdapterParameters;

        return $this;
    }

    public function createQuery(): Query
    {
        $query = new Query();

        if ([] !== $this->availableHitsPerPage) {
            $query->setActiveHitsPerPage(current($this->availableHitsPerPage));
        }

        if ([] !== $this->availableSorts) {
            $defaultSort = current($this->availableSorts);
            $query->setActiveSort($defaultSort->getKey());
        }

        return $query;
    }

    public function enableUrlRewriting(): static
    {
        $this->urlRewriting = true;

        return $this;
    }

    public function hasUrlRewriting(): bool
    {
        return $this->urlRewriting;
    }

    public function getUrlFormater(): string
    {
        return $this->urlFormater ?? DefaultUrlFormater::class;
    }

    public function setUrlFormater(string $urlFormater): static
    {
        $this->urlFormater = $urlFormater;

        return $this;
    }

    public function reset(): void
    {
        $this->availableHitsPerPage = [12];
        $this->availableSorts = [];
        $this->facets = [];
        $this->adapterParameters = [];
        $this->resolvedAdapterParameters = [];
        $this->urlRewriting = false;
        $this->urlFormater = null;
    }
}
