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

use Mezcalito\UxSearchBundle\Search\Filter\RangeFilter;
use PHPUnit\Framework\TestCase;

class RangeFilterTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $filter = new RangeFilter('price', 10.5, 50.0);

        $this->assertEquals('price', $filter->getProperty());
        $this->assertEquals(10.5, $filter->getMin());
        $this->assertEquals(50.0, $filter->getMax());
    }

    public function testSetMin(): void
    {
        $filter = new RangeFilter('price');
        $filter->setMin(20.0);

        $this->assertEquals(20.0, $filter->getMin());
    }

    public function testSetMax(): void
    {
        $filter = new RangeFilter('price');
        $filter->setMax(100.0);

        $this->assertEquals(100.0, $filter->getMax());
    }

    public function testHasValuesWhenBothValuesAreNull(): void
    {
        $filter = new RangeFilter('price');

        $this->assertFalse($filter->hasValues());
    }

    public function testHasValuesWhenOnlyMinIsSet(): void
    {
        $filter = new RangeFilter('price', 5);

        $this->assertTrue($filter->hasValues());
    }

    public function testHasValuesWhenOnlyMaxIsSet(): void
    {
        $filter = new RangeFilter('price', null, 30);

        $this->assertTrue($filter->hasValues());
    }

    public function testHasValuesWhenBothMinAndMaxAreSet(): void
    {
        $filter = new RangeFilter('price', 10, 50);

        $this->assertTrue($filter->hasValues());
    }
}
