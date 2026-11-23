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

use Mezcalito\UxSearchBundle\Context\ContextProvider;
use Mezcalito\UxSearchBundle\Search\AbstractSearch;
use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\ResultSet\ResultSet;
use Mezcalito\UxSearchBundle\Search\Searcher;
use Mezcalito\UxSearchBundle\Search\SearchInterface;
use Mezcalito\UxSearchBundle\Search\SearchProvider;
use Mezcalito\UxSearchBundle\Search\Url\UrlFormaterProvider;
use Mezcalito\UxSearchBundle\Twig\Components\Layout;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class LayoutPageRangeTest extends TestCase
{
    /**
     * @param list<int> $expectedSearchedPages
     */
    #[DataProvider('providePageCases')]
    public function testReRenderKeepsPageWithinResults(int $totalResults, int $requestedPage, int $expectedPage, array $expectedSearchedPages): void
    {
        $searcher = new PageRecordingSearcher($totalResults);
        $layout = $this->createLayout($searcher);

        $layout->onInitialMount(['name' => 'listing', 'options' => []]);

        $searcher->reset();
        $layout->name = 'listing';
        $layout->options = [];

        $layout->changeCurrentPage($requestedPage);
        $layout->onReRender();

        self::assertInstanceOf(Query::class, $layout->query);
        self::assertSame($expectedPage, $layout->query->getCurrentPage());
        self::assertSame($expectedSearchedPages, $searcher->getSearchedPages());
    }

    /**
     * @return iterable<string, array{int, int, int, list<int>}>
     */
    public static function providePageCases(): iterable
    {
        yield 'page within range is searched once' => [25, 2, 2, [2]];
        yield 'last page is searched once' => [25, 3, 3, [3]];
        yield 'page beyond last page falls back to last page' => [25, 99, 3, [99, 3]];
        yield 'no result keeps requested page' => [0, 5, 5, [5]];
    }

    private function createLayout(Searcher $searcher): PageRangeLayout
    {
        $search = new class extends AbstractSearch {
            public function build(array $options = []): void
            {
                $this->setAvailableHitsPerPage([10]);
            }
        };

        return new PageRangeLayout(
            new SearchProvider(['listing' => $search]),
            $searcher,
            new RequestStack(),
            new UrlFormaterProvider([]),
            new Serializer([new ObjectNormalizer()]),
            new ContextProvider(),
        );
    }
}

final class PageRangeLayout extends Layout
{
    public function dispatchBrowserEvent(string $event, array $data = []): void
    {
    }
}

readonly class PageRecordingSearcher extends Searcher
{
    /** @var \ArrayObject<int, int> */
    private \ArrayObject $pages;

    public function __construct(private int $totalResults)
    {
        $this->pages = new \ArrayObject();
    }

    public function reset(): void
    {
        $this->pages->exchangeArray([]);
    }

    /**
     * @return list<int>
     */
    public function getSearchedPages(): array
    {
        return array_values($this->pages->getArrayCopy());
    }

    public function search(Query $query, SearchInterface $search): ResultSet
    {
        $this->pages->append($query->getCurrentPage());

        return (new ResultSet())->setTotalResults($this->totalResults);
    }
}
