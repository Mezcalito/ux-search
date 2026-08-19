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
use Mezcalito\UxSearchBundle\Context\ContextProvider;
use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\ResultSet\ResultSet;
use Mezcalito\UxSearchBundle\Search\SearchInterface;
use Mezcalito\UxSearchBundle\Twig\Components\Pagination;
use PHPUnit\Framework\Attributes\DataProvider;
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

        $this->assertSame(9, substr_count($rendered->toString(), '<li class="ux-search-pagination__item">'));
        $this->assertStringContainsString('<span class="ux-search-pagination__link is-current">3</span>', $rendered->toString());
    }

    /**
     * @param list<int|null> $expected
     */
    #[DataProvider('providePagesCases')]
    public function testGetPages(int $totalPage, int $currentPage, array $expected): void
    {
        $provider = new ContextProvider();
        $provider->init((new Query())->setCurrentPage($currentPage)->setActiveHitsPerPage(1), $this->createStub(SearchInterface::class));
        $provider->getCurrentContext()->setResults((new ResultSet())->setTotalResults($totalPage));

        $this->assertSame($expected, (new Pagination($provider))->getPages());
    }

    /**
     * @return iterable<string, array{int, int, list<int|null>}>
     */
    public static function providePagesCases(): iterable
    {
        yield 'middle window with both ellipses hidden pages' => [10, 5, [1, 2, 3, 4, 5, 6, 7, null, 10]];
        yield 'near start keeps page five behind ellipsis' => [7, 2, [1, 2, 3, 4, null, 7]];
        yield 'first page' => [10, 1, [1, 2, 3, null, 10]];
        yield 'last page' => [10, 8, [1, null, 6, 7, 8, 9, 10]];
        yield 'gap of one page is shown instead of ellipsis' => [10, 4, [1, 2, 3, 4, 5, 6, null, 10]];
        yield 'small total renders all pages' => [3, 2, [1, 2, 3]];
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
