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

use Mezcalito\UxSearchBundle\Exception\SearchException;
use Mezcalito\UxSearchBundle\Search\AbstractSearch;
use Mezcalito\UxSearchBundle\Search\SearchProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class SearchProviderTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testProvider(): void
    {
        $one = $this->createStub(AbstractSearch::class);
        $one->method('getIndexName')->willReturn('one');
        $two = $this->createStub(AbstractSearch::class);
        $two->method('getIndexName')->willReturn('two');

        $provider = new SearchProvider(['one' => $one, 'two' => $two]);

        $this->assertSame($one, $provider->getSearch('one'));
        $this->assertSame($two, $provider->getSearch('two'));
    }

    public function testMissingProvider(): void
    {
        $provider = new SearchProvider([]);

        $this->expectException(SearchException::class);

        $provider->getSearch('missing');
    }
}
