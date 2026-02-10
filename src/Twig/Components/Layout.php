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

use Mezcalito\UxSearchBundle\Search\Filter\RangeFilter;
use Mezcalito\UxSearchBundle\Search\Filter\TermFilter;
use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\Searcher;
use Mezcalito\UxSearchBundle\Search\SearchInterface;
use Mezcalito\UxSearchBundle\Search\SearchProvider;
use Mezcalito\UxSearchBundle\Search\Url\CurrentRequest;
use Mezcalito\UxSearchBundle\Search\Url\UrlFormaterInterface;
use Mezcalito\UxSearchBundle\Search\Url\UrlFormaterProvider;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\PreReRender;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PreMount;

class Layout
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp(
        writable: ['queryString', 'activeSort', 'activeHitsPerPage'],
        useSerializerForHydration: true,
        onUpdated: ['queryString' => 'resetCurrentPage', 'activeSort' => 'resetCurrentPage', 'activeHitsPerPage' => 'resetCurrentPage']
    )]
    public ?Query $query = null;

    #[LiveProp]
    public ?string $name = null;

    /**
     * @var array<string, mixed>
     */
    #[LiveProp]
    public array $options = [];

    public ?SearchInterface $search = null;

    #[LiveProp(useSerializerForHydration: true)]
    public ?CurrentRequest $currentRequest = null;

    public function __construct(
        private readonly SearchProvider $searchConfigurationProvider,
        private readonly Searcher $searcher,
        private readonly RequestStack $requestStack,
        private readonly UrlFormaterProvider $urlFormaterProvider,
        /** @var Serializer */
        private readonly SerializerInterface $serializer,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    #[PreMount]
    public function onInitialMount(array $data): void
    {
        $this->search = $this->getSearch($data['name'])->create($data['options'] ?? []);
        $this->query = $this->getSearch($data['name'])->createQuery();

        if ($this->search->hasUrlRewriting()) {
            $mainRequest = $this->requestStack->getMainRequest();

            if ($mainRequest && $mainRequest->attributes->has('_route')) {
                $this->currentRequest = CurrentRequest::fromRequest($mainRequest);
                $this->getUrlFormater()->applyFilters($this->currentRequest, $this->search, $this->query);
            }
        }

        $this->searcher->search($this->query, $this->search);
    }

    #[PreReRender]
    public function onReRender(): void
    {
        $this->search = $this->getSearch($this->name)->create($this->options);
        $this->searcher->search($this->query, $this->search);

        $this->dispatchBrowserEvent('ux-search:query:update', $this->serializer->normalize($this->query));

        if ($this->search->hasUrlRewriting() && $this->currentRequest) {
            $this->dispatchBrowserEvent('ux-search:url:update', [
                'url' => $this->getUrlFormater()->generateUrl($this->currentRequest, $this->search, $this->query),
            ]);
        }
    }

    #[LiveAction]
    public function changeCurrentPage(#[LiveArg] int $page): void
    {
        $this->query->setCurrentPage($page);
    }

    public function resetCurrentPage(string|int|null $previousValue): void
    {
        $this->query->setCurrentPage(1);
    }

    #[LiveAction]
    public function switchFacetTerm(#[LiveArg] string $property, #[LiveArg] string $value): void
    {
        $filter = $this->query->getActiveFilter($property);

        if (!$filter instanceof TermFilter) {
            $filter = new TermFilter($property);
            $this->query->addActiveFilter($filter);
        }

        if ($filter->hasValue($value)) {
            $filter->setValues([]);
        } else {
            $filter->setValues([$value]);
        }

        if (!$filter->hasValues()) {
            $this->query->removeActiveFilter($filter);
        }

        $this->query->setCurrentPage(1);
    }

    #[LiveAction]
    public function toggleFacetTerm(#[LiveArg] string $property, #[LiveArg] string $value): void
    {
        $filter = $this->query->getActiveFilter($property);

        if (!$filter instanceof TermFilter) {
            $filter = new TermFilter($property);
            $this->query->addActiveFilter($filter);
        }

        $filter->toggleValue($value);

        if (!$filter->hasValues()) {
            $this->query->removeActiveFilter($filter);
        }

        $this->query->setCurrentPage(1);
    }

    #[LiveAction]
    public function updateFacetRange(#[LiveArg] string $property, #[LiveArg] float|int|null $min, #[LiveArg] float|int|null $max): void
    {
        $filter = $this->query->getActiveFilter($property);

        if (!$filter instanceof RangeFilter) {
            $filter = new RangeFilter($property);
            $this->query->addActiveFilter($filter);
        }

        $filter->setMin($min);
        $filter->setMax($max);

        if (!$filter->hasValues()) {
            $this->query->removeActiveFilter($filter);
        }

        $this->query->setCurrentPage(1);
    }

    #[LiveAction]
    public function clearRefinements(): void
    {
        $this->query->setActiveFilters([]);
    }

    private function getSearch(string $name): SearchInterface
    {
        return $this->searchConfigurationProvider->getSearch($name);
    }

    private function getUrlFormater(): UrlFormaterInterface
    {
        return $this->urlFormaterProvider->getUrlFormater($this->search->getUrlFormater());
    }
}
