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
}
