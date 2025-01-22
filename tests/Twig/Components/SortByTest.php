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
use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\SearchInterface;
use Mezcalito\UxSearchBundle\Search\Sort;
use Mezcalito\UxSearchBundle\Twig\Components\SortBy;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;

class SortByTest extends AbstractComponentTestCase
{
    use InteractsWithTwigComponents;

    public function testComponentRenders(): void
    {
        $search = $this->createStub(SearchInterface::class);
        $search->method('getIndexName')->willReturn('test');
        $search->method('getAvailableSorts')->willReturn([
            new Sort(null, 'Default'),
            new Sort('price:desc', 'Price desc'),
            new Sort('price:asc', 'Price asc'),
        ]);

        $context = new Context();
        $context->setQuery((new Query())->setActiveSort('price:desc'));
        $context->setSearch($search);
        $this->setCurrentContext($context);

        $rendered = $this->renderTwigComponent(
            name: SortBy::class,
        );

        $this->assertStringContainsString('<option value="" >Default</option>', $rendered->toString());
        $this->assertStringContainsString('<option value="price:desc" selected>Price desc</option>', $rendered->toString());
        $this->assertStringContainsString('<option value="price:asc" >Price asc</option>', $rendered->toString());
    }
}
