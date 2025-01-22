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

namespace Mezcalito\UxSearchBundle\Tests\Context;

use Mezcalito\UxSearchBundle\Context\ContextProvider;
use Mezcalito\UxSearchBundle\Exception\ContextException;
use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\ResultSet\ResultSet;
use Mezcalito\UxSearchBundle\Search\Searcher;
use Mezcalito\UxSearchBundle\Search\SearchInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class ContextProviderTest extends TestCase
{
    private ContextProvider $contextProvider;

    private SearchInterface $search;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $searcher = $this->createStub(Searcher::class);
        $searcher->method('search')->willReturn(new ResultSet());

        $this->search = $this->createStub(SearchInterface::class);
        $this->search->method('getIndexName')->willReturn('test');

        $this->contextProvider = new ContextProvider();
    }

    public function testInit()
    {
        $query = new Query();
        $this->contextProvider->init($query, $this->search);

        $this->assertTrue($this->contextProvider->hasCurrentContext());
        $this->assertSame($query, $this->contextProvider->getCurrentContext()->getQuery());
        $this->assertSame($this->search, $this->contextProvider->getCurrentContext()->getSearch());
        $this->assertNull($this->contextProvider->getCurrentContext()->getResults());
    }

    public function testBeforeInit()
    {
        $this->assertFalse($this->contextProvider->hasCurrentContext());

        $this->expectException(ContextException::class);

        $this->contextProvider->getCurrentContext();
    }
}
