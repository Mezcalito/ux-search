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

use Mezcalito\UxSearchBundle\Search\Filter\TermFilter;
use Mezcalito\UxSearchBundle\Search\Query;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{
    private Query $query;

    protected function setUp(): void
    {
        $this->query = new Query();
    }

    public function testSetActiveFilters(): void
    {
        $colorFilter = new TermFilter('color', ['red', 'green']);
        $this->query->setActiveFilters([$colorFilter]);

        $this->assertTrue($this->query->hasActiveFilter('color'));
        $this->assertSame($colorFilter, $this->query->getActiveFilter('color'));
    }

    public function testAddActiveFilters(): void
    {
        $colorFilter = new TermFilter('color', ['red', 'green']);
        $this->query->addActiveFilter($colorFilter);

        $this->assertTrue($this->query->hasActiveFilter('color'));
        $this->assertSame($colorFilter, $this->query->getActiveFilter('color'));
    }
}
