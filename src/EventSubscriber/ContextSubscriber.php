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

namespace Mezcalito\UxSearchBundle\EventSubscriber;

use Mezcalito\UxSearchBundle\Context\ContextProvider;
use Mezcalito\UxSearchBundle\Event\PostSearchEvent;
use Mezcalito\UxSearchBundle\Event\PreSearchEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class ContextSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ContextProvider $contextProvider,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PreSearchEvent::class => 'onPreSearchEvent',
            PostSearchEvent::class => 'onPostSearchEvent',
        ];
    }

    public function onPreSearchEvent(PreSearchEvent $event): void
    {
        $this->contextProvider->init($event->getQuery(), $event->getSearch());
    }

    public function onPostSearchEvent(PostSearchEvent $event): void
    {
        $this->contextProvider->getCurrentContext()->setResults($event->getResultSet());
    }
}
