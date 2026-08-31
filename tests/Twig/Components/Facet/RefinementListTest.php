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

namespace Mezcalito\UxSearchBundle\Tests\Twig\Components\Facet;

use Mezcalito\UxSearchBundle\Context\Context;
use Mezcalito\UxSearchBundle\Search\Facet;
use Mezcalito\UxSearchBundle\Search\Filter\TermFilter;
use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\ResultSet\FacetTermDistribution;
use Mezcalito\UxSearchBundle\Search\ResultSet\ResultSet;
use Mezcalito\UxSearchBundle\Search\SearchInterface;
use Mezcalito\UxSearchBundle\Tests\Twig\Components\AbstractComponentTestCase;
use Mezcalito\UxSearchBundle\Twig\Components\Facet\RefinementList;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;

class RefinementListTest extends AbstractComponentTestCase
{
    use InteractsWithTwigComponents;

    public function testComponentRenders(): void
    {
        $search = $this->createStub(SearchInterface::class);
        $search->method('getFacet')
            ->willReturn(new Facet('brand', 'Brand'));

        $context = new Context();
        $context->setQuery((new Query())->addActiveFilter(new TermFilter('brand')));
        $context->setSearch($search);
        $context->setResults((new ResultSet())->setFacetDistributions([
            (new FacetTermDistribution())
                ->setProperty('brand')
                ->setValues([
                    'GoPro' => 10,
                    'Apple' => 50,
                    'Samsung' => 20,
                ])
                ->setCheckedValues(['Apple']),
        ]));

        $this->setCurrentContext($context);

        $rendered = $this->renderTwigComponent(
            name: RefinementList::class,
            data: ['property' => 'brand'],
        );

        // Label
        $this->assertStringContainsString('<legend class="ux-search-facet__title ux-search-refinement-list__title">Brand</legend>', $rendered->toString());

        // GoPro
        $this->assertStringContainsString('<label class="ux-search-refinement-list__label" for="brand-GoPro">', $rendered->toString());
        $this->assertStringContainsString('<span class="ux-search-refinement-list__label-text">GoPro</span>', $rendered->toString());
        $this->assertStringContainsString('<span class="ux-search-refinement-list__count">10</span>', $rendered->toString());

        // Apple
        $this->assertStringContainsString('<label class="ux-search-refinement-list__label" for="brand-Apple">', $rendered->toString());
        $this->assertStringContainsString('<span class="ux-search-refinement-list__label-text">Apple</span>', $rendered->toString());
        $this->assertStringContainsString('<span class="ux-search-refinement-list__count">50</span>', $rendered->toString());
        $this->assertStringContainsString('id="brand-Apple" checked data-action="live#action"', $rendered->toString());

        // Samsung
        $this->assertStringContainsString('<label class="ux-search-refinement-list__label" for="brand-Samsung">', $rendered->toString());
        $this->assertStringContainsString('<span class="ux-search-refinement-list__label-text">Samsung</span>', $rendered->toString());
        $this->assertStringContainsString('<span class="ux-search-refinement-list__count">20</span>', $rendered->toString());
    }

    public function testSortingIsDisabledByDefault(): void
    {
        $search = $this->createStub(SearchInterface::class);
        $search->method('getFacet')
            ->willReturn(new Facet('brand', 'Brand'));

        $context = new Context();
        $context->setQuery(new Query());
        $context->setSearch($search);
        $context->setResults((new ResultSet())->setFacetDistributions([
            (new FacetTermDistribution())
                ->setProperty('brand')
                ->setValues(['Apple' => 50, 'GoPro' => 10])
                ->setCheckedValues([]),
        ]));

        $this->setCurrentContext($context);

        $rendered = $this->renderTwigComponent(
            name: RefinementList::class,
            data: ['property' => 'brand'],
        );

        $this->assertStringNotContainsString('ux-search-refinement-list__sort', $rendered->toString());
        $this->assertStringNotContainsString('By count', $rendered->toString());
        $this->assertStringNotContainsString('By name', $rendered->toString());
    }

    public function testSortingCanBeEnabled(): void
    {
        $search = $this->createStub(SearchInterface::class);
        $search->method('getFacet')
            ->willReturn(new Facet('brand', 'Brand', props: ['enableSort' => true]));

        $context = new Context();
        $context->setQuery(new Query());
        $context->setSearch($search);
        $context->setResults((new ResultSet())->setFacetDistributions([
            (new FacetTermDistribution())
                ->setProperty('brand')
                ->setValues(['Apple' => 50, 'GoPro' => 10])
                ->setCheckedValues([]),
        ]));

        $this->setCurrentContext($context);

        $rendered = $this->renderTwigComponent(
            name: RefinementList::class,
            data: ['property' => 'brand'],
        );

        $this->assertStringContainsString('ux-search-refinement-list__sort', $rendered->toString());
        $this->assertStringContainsString('<select', $rendered->toString());
        $this->assertStringContainsString('data-ux-search--refinement-list-target="sortSelect"', $rendered->toString());
        $this->assertStringContainsString('data-action="change->ux-search--refinement-list#changeSort change->ux-search--refinement-list#syncLiveAction"', $rendered->toString());
        $this->assertStringContainsString('data-ux-search--refinement-list-property-value="brand"', $rendered->toString());
        $this->assertStringContainsString('<option value="count"', $rendered->toString());
        $this->assertStringContainsString('<option value="name"', $rendered->toString());
        $this->assertStringContainsString('By count</option>', $rendered->toString());
        $this->assertStringContainsString('By name</option>', $rendered->toString());
    }

    public function testDefaultSortByIsCount(): void
    {
        $search = $this->createStub(SearchInterface::class);
        $search->method('getFacet')
            ->willReturn(new Facet('brand', 'Brand', props: ['enableSort' => true]));

        $context = new Context();
        $context->setQuery(new Query());
        $context->setSearch($search);
        $context->setResults((new ResultSet())->setFacetDistributions([
            (new FacetTermDistribution())
                ->setProperty('brand')
                ->setValues(['Apple' => 50])
                ->setCheckedValues([]),
        ]));

        $this->setCurrentContext($context);

        $rendered = $this->renderTwigComponent(
            name: RefinementList::class,
            data: ['property' => 'brand'],
        );

        $this->assertStringContainsString('data-ux-search--refinement-list-sort-by-value="count"', $rendered->toString());
        $this->assertStringContainsString('<option value="count" selected>By count</option>', $rendered->toString());
    }

    public function testCustomDefaultSortBy(): void
    {
        $search = $this->createStub(SearchInterface::class);
        $search->method('getFacet')
            ->willReturn(new Facet('brand', 'Brand', props: ['enableSort' => true, 'sortBy' => 'name']));

        $context = new Context();
        $context->setQuery(new Query());
        $context->setSearch($search);
        $context->setResults((new ResultSet())->setFacetDistributions([
            (new FacetTermDistribution())
                ->setProperty('brand')
                ->setValues(['Apple' => 50])
                ->setCheckedValues([]),
        ]));

        $this->setCurrentContext($context);

        $rendered = $this->renderTwigComponent(
            name: RefinementList::class,
            data: ['property' => 'brand'],
        );

        $this->assertStringContainsString('data-ux-search--refinement-list-sort-by-value="name"', $rendered->toString());
        $this->assertStringContainsString('<option value="name" selected>By name</option>', $rendered->toString());
    }

    public function testItemsHaveRequiredDataAttributesForSorting(): void
    {
        $search = $this->createStub(SearchInterface::class);
        $search->method('getFacet')
            ->willReturn(new Facet('brand', 'Brand', props: ['enableSort' => true]));

        $context = new Context();
        $context->setQuery(new Query());
        $context->setSearch($search);
        $context->setResults((new ResultSet())->setFacetDistributions([
            (new FacetTermDistribution())
                ->setProperty('brand')
                ->setValues(['Apple' => 50, 'GoPro' => 10])
                ->setCheckedValues([]),
        ]));

        $this->setCurrentContext($context);

        $rendered = $this->renderTwigComponent(
            name: RefinementList::class,
            data: ['property' => 'brand'],
        );

        // Check that items have the required data attributes
        $this->assertStringContainsString('data-ux-search--refinement-list-target="item"', $rendered->toString());
        $this->assertStringContainsString('data-facet-value="Apple"', $rendered->toString());
        $this->assertStringContainsString('data-facet-count="50"', $rendered->toString());
        $this->assertStringContainsString('data-facet-value="GoPro"', $rendered->toString());
        $this->assertStringContainsString('data-facet-count="10"', $rendered->toString());
    }

    public function testSearchAndSortCanBeEnabledTogether(): void
    {
        $search = $this->createStub(SearchInterface::class);
        $search->method('getFacet')
            ->willReturn(new Facet('brand', 'Brand', props: ['enableSearch' => true, 'enableSort' => true]));

        $context = new Context();
        $context->setQuery(new Query());
        $context->setSearch($search);
        $context->setResults((new ResultSet())->setFacetDistributions([
            (new FacetTermDistribution())
                ->setProperty('brand')
                ->setValues(['Apple' => 50])
                ->setCheckedValues([]),
        ]));

        $this->setCurrentContext($context);

        $rendered = $this->renderTwigComponent(
            name: RefinementList::class,
            data: ['property' => 'brand'],
        );

        // Check both features are present
        $this->assertStringContainsString('ux-search-refinement-list__search', $rendered->toString());
        $this->assertStringContainsString('ux-search-refinement-list__sort', $rendered->toString());
        $this->assertStringContainsString('data-ux-search--refinement-list-target="input"', $rendered->toString());
        $this->assertStringContainsString('data-ux-search--refinement-list-target="sortSelect"', $rendered->toString());
    }

    public function testSortByIsRestoredFromQueryFacetSortPreference(): void
    {
        $search = $this->createStub(SearchInterface::class);
        $search->method('getFacet')
            ->willReturn(new Facet('brand', 'Brand', props: ['enableSort' => true, 'sortBy' => 'count']));

        $query = new Query();
        $query->setFacetSortPreference('brand', 'name');

        $context = new Context();
        $context->setQuery($query);
        $context->setSearch($search);
        $context->setResults((new ResultSet())->setFacetDistributions([
            (new FacetTermDistribution())
                ->setProperty('brand')
                ->setValues(['Apple' => 50, 'GoPro' => 10])
                ->setCheckedValues([]),
        ]));

        $this->setCurrentContext($context);

        $rendered = $this->renderTwigComponent(
            name: RefinementList::class,
            data: ['property' => 'brand'],
        );

        // Verify that the sort preference from Query (which comes from URL) overrides the default
        $this->assertStringContainsString('data-ux-search--refinement-list-sort-by-value="name"', $rendered->toString());
        $this->assertStringContainsString('<option value="name" selected>By name</option>', $rendered->toString());
        $this->assertStringNotContainsString('<option value="count" selected>', $rendered->toString());
    }

    public function testSortIsHiddenWhenNoFacetsAvailable(): void
    {
        $search = $this->createStub(SearchInterface::class);
        $search->method('getFacet')
            ->willReturn(new Facet('brand', 'Brand', props: ['enableSort' => true]));

        $context = new Context();
        $context->setQuery(new Query());
        $context->setSearch($search);
        $context->setResults((new ResultSet())->setFacetDistributions([
            (new FacetTermDistribution())
                ->setProperty('brand')
                ->setValues([])
                ->setCheckedValues([]),
        ]));

        $this->setCurrentContext($context);

        $rendered = $this->renderTwigComponent(
            name: RefinementList::class,
            data: ['property' => 'brand'],
        );

        $this->assertStringNotContainsString('ux-search-refinement-list__sort', $rendered->toString());
        $this->assertStringNotContainsString('data-ux-search--refinement-list-target="sortSelect"', $rendered->toString());
    }

    public function testSearchIsHiddenWhenNoFacetsAvailable(): void
    {
        $search = $this->createStub(SearchInterface::class);
        $search->method('getFacet')
            ->willReturn(new Facet('brand', 'Brand', props: ['enableSearch' => true]));

        $context = new Context();
        $context->setQuery(new Query());
        $context->setSearch($search);
        $context->setResults((new ResultSet())->setFacetDistributions([
            (new FacetTermDistribution())
                ->setProperty('brand')
                ->setValues([])
                ->setCheckedValues([]),
        ]));

        $this->setCurrentContext($context);

        $rendered = $this->renderTwigComponent(
            name: RefinementList::class,
            data: ['property' => 'brand'],
        );

        $this->assertStringNotContainsString('ux-search-refinement-list__search', $rendered->toString());
        $this->assertStringNotContainsString('data-ux-search--refinement-list-target="input"', $rendered->toString());
    }

    public function testSearchAndSortAreHiddenWhenNoFacetsAvailable(): void
    {
        $search = $this->createStub(SearchInterface::class);
        $search->method('getFacet')
            ->willReturn(new Facet('brand', 'Brand', props: ['enableSearch' => true, 'enableSort' => true]));

        $context = new Context();
        $context->setQuery(new Query());
        $context->setSearch($search);
        $context->setResults((new ResultSet())->setFacetDistributions([
            (new FacetTermDistribution())
                ->setProperty('brand')
                ->setValues([])
                ->setCheckedValues([]),
        ]));

        $this->setCurrentContext($context);

        $rendered = $this->renderTwigComponent(
            name: RefinementList::class,
            data: ['property' => 'brand'],
        );

        $this->assertStringNotContainsString('ux-search-refinement-list__search', $rendered->toString());
        $this->assertStringNotContainsString('ux-search-refinement-list__sort', $rendered->toString());
        $this->assertStringNotContainsString('data-ux-search--refinement-list-target="input"', $rendered->toString());
        $this->assertStringNotContainsString('data-ux-search--refinement-list-target="sortSelect"', $rendered->toString());
    }
}
