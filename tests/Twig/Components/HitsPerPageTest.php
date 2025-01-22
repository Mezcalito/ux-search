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
use Mezcalito\UxSearchBundle\Twig\Components\HitsPerPage;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;

class HitsPerPageTest extends AbstractComponentTestCase
{
    use InteractsWithTwigComponents;

    public function testComponentRenders(): void
    {
        $search = $this->createStub(SearchInterface::class);
        $search->method('getIndexName')->willReturn('test');
        $search->method('getAvailableHitsPerPage')->willReturn([2, 4, 6]);

        $context = new Context();
        $context->setQuery((new Query())->setActiveHitsPerPage(4));
        $context->setSearch($search);
        $this->setCurrentContext($context);

        $rendered = $this->renderTwigComponent(
            name: HitsPerPage::class,
        );

        $this->assertStringContainsString('<option value="4" selected>4</option>', $rendered->toString());
        $this->assertStringContainsString('<option value="6" >6</option>', $rendered->toString());
    }
}
