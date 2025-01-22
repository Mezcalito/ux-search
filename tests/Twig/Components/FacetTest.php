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

namespace Mezcalito\UxSearchBundle\Tests\Twig\Components;

use Mezcalito\UxSearchBundle\Context\Context;
use Mezcalito\UxSearchBundle\Search\Filter\RangeFilter;
use Mezcalito\UxSearchBundle\Search\Filter\TermFilter;
use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\ResultSet\FacetStat;
use Mezcalito\UxSearchBundle\Search\ResultSet\FacetTermDistribution;
use Mezcalito\UxSearchBundle\Search\ResultSet\ResultSet;
use Mezcalito\UxSearchBundle\Search\SearchInterface;
use Mezcalito\UxSearchBundle\Twig\Components\Facet;
use Mezcalito\UxSearchBundle\Twig\Components\Facet\RangeInput;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;

class FacetTest extends AbstractComponentTestCase
{
    use InteractsWithTwigComponents;

    public function testComponentRenders(): void
    {
        $search = $this->createStub(SearchInterface::class);
        $search->method('getFacet')
            ->willReturn(new \Mezcalito\UxSearchBundle\Search\Facet('brand', 'Brand'));

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
            name: Facet::class,
            data: ['property' => 'brand']
        );

        $this->assertStringContainsString('ux-search-refinement-list', $rendered->toString());
    }

    public function testComponentRendersWithDisplayCompnent(): void
    {
        $search = $this->createStub(SearchInterface::class);
        $search->method('getFacet')
            ->willReturn(new \Mezcalito\UxSearchBundle\Search\Facet('price', 'Price', RangeInput::class));

        $context = new Context();
        $context->setQuery((new Query())->addActiveFilter(new RangeFilter('price')));
        $context->setSearch($search);
        $context->setResults((new ResultSet())->setFacetStats([
            new FacetStat('price', 0, 50, 20, 30),
        ]));

        $this->setCurrentContext($context);

        $rendered = $this->renderTwigComponent(
            name: Facet::class,
            data: ['property' => 'price']
        );

        $this->assertStringContainsString('ux-search-range-input', $rendered->toString());
    }
}
