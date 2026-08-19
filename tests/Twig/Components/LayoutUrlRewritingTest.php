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
use Mezcalito\UxSearchBundle\Search\Searcher as BaseSearcher;
use Mezcalito\UxSearchBundle\Search\SearchInterface;
use Mezcalito\UxSearchBundle\Search\SearchProvider;
use Mezcalito\UxSearchBundle\Search\Url\CurrentRequest;
use Mezcalito\UxSearchBundle\Search\Url\UrlFormaterInterface;
use Mezcalito\UxSearchBundle\Search\Url\UrlFormaterProvider;
use Mezcalito\UxSearchBundle\Twig\Components\Layout;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class LayoutUrlRewritingTest extends TestCase
{
    public function testDoesNotDispatchWhenUrlRewritingDisabled(): void
    {
        $layout = $this->createLayout(enabled: false);

        $layout->onInitialMount(['name' => 'listing', 'options' => []]);

        $layout->name = 'listing';
        $layout->options = [];
        $layout->onReRender();

        self::assertCount(1, $layout->dispatchedEvents);
        self::assertSame('ux-search:query:update', $layout->dispatchedEvents[0][0]);

        // Verify that ux-search:url:update is NOT dispatched
        foreach ($layout->dispatchedEvents as $event) {
            self::assertNotSame('ux-search:url:update', $event[0]);
        }
    }

    public function testDispatchesNamespacedEventWhenEnabled(): void
    {
        $layout = $this->createLayout(enabled: true);

        $layout->onInitialMount(['name' => 'listing', 'options' => []]);

        $layout->name = 'listing';
        $layout->options = [];
        $layout->onReRender();

        self::assertCount(2, $layout->dispatchedEvents);

        // First event should be ux-search:query:update
        self::assertSame('ux-search:query:update', $layout->dispatchedEvents[0][0]);

        // Second event should be ux-search:url:update
        self::assertSame('ux-search:url:update', $layout->dispatchedEvents[1][0]);
        self::assertSame(['url' => 'https://example.test/route?ok=1'], $layout->dispatchedEvents[1][1]);
    }

    private function createLayout(bool $enabled): TestableLayout
    {
        $search = new class extends AbstractSearch {
            public bool $rewriting = false;

            public function build(array $options = []): void
            {
                if ($this->rewriting) {
                    $this->enableUrlRewriting()->setUrlFormater(TestUrlFormater::class);
                }
            }
        };
        $search->rewriting = $enabled;

        $provider = new SearchProvider(['listing' => $search]);

        $searcher = new DummySearcher();

        $request = new Request([], [], ['_route' => 'route']);
        $stack = new RequestStack();
        $stack->push($request);

        $urlFormaterProvider = new UrlFormaterProvider([
            TestUrlFormater::class => new TestUrlFormater(),
        ]);

        $normalizers = [
            new ObjectNormalizer(
                null,
                null,
                null,
                new ReflectionExtractor()
            ),
        ];

        $encoders = [new JsonEncoder()];

        $serializer = new Serializer($normalizers, $encoders);

        return new TestableLayout($provider, $searcher, $stack, $urlFormaterProvider, $serializer, new ContextProvider());
    }
}

final class TestableLayout extends Layout
{
    public array $dispatchedEvents = [];

    public function dispatchBrowserEvent(string $event, array $data = []): void
    {
        $this->dispatchedEvents[] = [$event, $data];
    }
}

readonly class DummySearcher extends BaseSearcher
{
    public function __construct()
    {
    }

    public function search(Query $query, SearchInterface $search): ResultSet
    {
        return new ResultSet();
    }
}

final class TestUrlFormater implements UrlFormaterInterface
{
    public function generateUrl(CurrentRequest $currentRequest, SearchInterface $search, Query $query): string
    {
        return 'https://example.test/'.$currentRequest->route.'?ok=1';
    }

    public function applyFilters(CurrentRequest $currentRequest, SearchInterface $search, Query $query): void
    {
        // no-op for this test
    }
}
