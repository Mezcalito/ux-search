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

namespace Mezcalito\UxSearchBundle\Tests\Search\Filter;

use Mezcalito\UxSearchBundle\Search\Filter\TermFilter;
use PHPUnit\Framework\TestCase;

class TermFilterTest extends TestCase
{
    private TermFilter $filter;

    protected function setUp(): void
    {
        $this->filter = (new TermFilter('test', ['green', 'red']));
    }

    public function testAdd(): void
    {
        $this->assertFalse($this->filter->hasValue('black'));
        $this->filter->addValue('black');
        $this->assertCount(3, $this->filter->getValues());
        $this->assertTrue($this->filter->hasValue('black'));
    }

    public function testRemove(): void
    {
        $this->assertTrue($this->filter->hasValue('red'));
        $this->filter->removeValue('red');
        $this->assertCount(1, $this->filter->getValues());
        $this->assertFalse($this->filter->hasValue('red'));
    }

    public function testToggle(): void
    {
        $this->assertTrue($this->filter->hasValue('red'));
        $this->filter->toggleValue('red');
        $this->assertFalse($this->filter->hasValue('red'));
        $this->filter->toggleValue('red');
        $this->assertTrue($this->filter->hasValue('red'));
    }
}
