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

namespace Mezcalito\UxSearchBundle\Tests\Search;

use Mezcalito\UxSearchBundle\Adapter\AdapterInterface;
use Mezcalito\UxSearchBundle\Adapter\AdapterProvider;
use Mezcalito\UxSearchBundle\Context\ContextProvider;
use Mezcalito\UxSearchBundle\Event\PostSearchEvent;
use Mezcalito\UxSearchBundle\Event\PreSearchEvent;
use Mezcalito\UxSearchBundle\EventSubscriber\ContextSubscriber;
use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\ResultSet\ResultSet;
use Mezcalito\UxSearchBundle\Search\Searcher;
use Mezcalito\UxSearchBundle\Search\SearchInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SearcherTest extends TestCase
{
    public function testSearch(): void
    {
        $query = new Query();
        $resultSet = new ResultSet();

        $search = $this->createStub(SearchInterface::class);
        $search->method('getIndexName')->willReturn('test');
        $search->method('getAvailableHitsPerPage')->willReturn([8]);
        $search->method('getResolvedAdapterParameters')->willReturn([
            'attributesToCrop' => ['description'],
        ]);

        $eventDispatcher = new EventDispatcher();

        $preListenerCalled = false;
        $availableHitsPerPageOnPreSearch = null;
        $preListener = function (PreSearchEvent $event) use (&$preListenerCalled, &$availableHitsPerPageOnPreSearch) {
            $preListenerCalled = true;
            $event->getQuery()->setQueryString('modified by preListener');
            $availableHitsPerPageOnPreSearch = $event->getSearch()->getAvailableHitsPerPage();
        };
        $eventDispatcher->addListener(PreSearchEvent::class, $preListener);

        $postListenerCalled = false;
        $postListener = function (PostSearchEvent $event) use (&$postListenerCalled) {
            $postListenerCalled = true;
            $event->getResultSet()->setTotalResults(99);

            $this->assertSame([8], $event->getSearch()->getAvailableHitsPerPage());
            $this->assertEquals('modified by preListener', $event->getQuery()->getQueryString());
        };
        $eventDispatcher->addListener(PostSearchEvent::class, $postListener);

        $postSubscriber = new class implements EventSubscriberInterface {
            public static function getSubscribedEvents(): array
            {
                return [
                    PostSearchEvent::class => 'onPostSearch',
                ];
            }

            public function onPostSearch(PostSearchEvent $event): void
            {
                $event->getResultSet()->setIndexUid('modifiedOnPostSearchSubscriber');
            }
        };
        $eventDispatcher->addSubscriber($postSubscriber);

        $adapter = $this->createMock(AdapterInterface::class);
        $adapter
            ->method('search')
            ->with($query, $search)
            ->willReturn($resultSet);

        $adapterProvider = $this->createMock(AdapterProvider::class);
        $adapterProvider
            ->method('getAdapter')
            ->willReturn($adapter);

        $contextProvider = $this->createStub(ContextProvider::class);

        $subscriber = new ContextSubscriber($contextProvider);
        $eventDispatcher->addSubscriber($subscriber);

        $search->method('getEventDispatcher')->willReturn($eventDispatcher);

        $searcher = new Searcher($adapterProvider, $contextProvider);

        $rs = $searcher->search($query, $search);

        $this->assertSame($rs, $resultSet);
        $this->assertTrue($preListenerCalled);
        $this->assertTrue($postListenerCalled);
        $this->assertEquals('modified by preListener', $query->getQueryString());
        $this->assertEquals(99, $rs->getTotalResults());
        $this->assertSame([8], $availableHitsPerPageOnPreSearch);
        $this->assertEquals('modifiedOnPostSearchSubscriber', $rs->getIndexUid());
        $this->assertEquals(['attributesToCrop' => ['description']], $search->getResolvedAdapterParameters());
    }
}
