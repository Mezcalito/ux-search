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
use Mezcalito\UxSearchBundle\Search\ResultSet\ResultSet;
use Mezcalito\UxSearchBundle\Twig\Components\Pagination;
use Symfony\UX\TwigComponent\Test\InteractsWithTwigComponents;

class PaginationTest extends AbstractComponentTestCase
{
    use InteractsWithTwigComponents;

    public function testComponentRenders(): void
    {
        $context = new Context();
        $context->setQuery((new Query())->setCurrentPage(3));
        $context->setResults((new ResultSet())->setTotalResults(100));
        $this->setCurrentContext($context);

        $rendered = $this->renderTwigComponent(
            name: Pagination::class,
        );

        $this->assertSame(10, substr_count($rendered->toString(), '<li class="ux-search-pagination__item">'));
        $this->assertStringContainsString('<span class="ux-search-pagination__link is-current">3</span>', $rendered->toString());
    }

    public function testComponentRendersWithoutPagination(): void
    {
        $context = new Context();
        $context->setQuery((new Query())->setCurrentPage(1));
        $context->setResults((new ResultSet())->setTotalResults(2));
        $this->setCurrentContext($context);

        $rendered = $this->renderTwigComponent(
            name: Pagination::class,
        );

        $this->assertEmpty($rendered->toString());
    }
}
