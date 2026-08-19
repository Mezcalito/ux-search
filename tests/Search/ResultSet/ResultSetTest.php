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

namespace Mezcalito\UxSearchBundle\Tests\Search\ResultSet;

use Mezcalito\UxSearchBundle\Exception\ResultSetException;
use Mezcalito\UxSearchBundle\Search\ResultSet\FacetStat;
use Mezcalito\UxSearchBundle\Search\ResultSet\FacetTermDistribution;
use Mezcalito\UxSearchBundle\Search\ResultSet\Hit;
use Mezcalito\UxSearchBundle\Search\ResultSet\ResultSet;
use PHPUnit\Framework\TestCase;

class ResultSetTest extends TestCase
{
    public function testDefaults(): void
    {
        $resultSet = new ResultSet();

        $this->assertNull($resultSet->getIndexUid());
        $this->assertSame([], $resultSet->getHits());
        $this->assertSame(0, $resultSet->getTotalResults());
        $this->assertSame([], $resultSet->getFacetDistributions());
        $this->assertSame([], $resultSet->getFacetStats());
    }

    public function testHitsAndTotals(): void
    {
        $hit = new Hit(['id' => 1], 1.0);

        $resultSet = (new ResultSet())
            ->setIndexUid('products')
            ->setHits([$hit])
            ->setTotalResults(42);

        $this->assertSame('products', $resultSet->getIndexUid());
        $this->assertSame([$hit], $resultSet->getHits());
        $this->assertSame(42, $resultSet->getTotalResults());
    }

    public function testFacetDistributionsAreIndexedByProperty(): void
    {
        $category = (new FacetTermDistribution())->setProperty('category')->setValues(['books' => 3]);
        $brand = (new FacetTermDistribution())->setProperty('brand')->setValues(['acme' => 1]);

        $resultSet = (new ResultSet())->setFacetDistributions([$category, $brand]);

        $this->assertSame(['category', 'brand'], array_keys($resultSet->getFacetDistributions()));
        $this->assertSame($category, $resultSet->getFacetDistribution('category'));
        $this->assertSame($brand, $resultSet->getFacetDistribution('brand'));
    }

    public function testUnknownFacetDistributionThrows(): void
    {
        $this->expectException(ResultSetException::class);

        (new ResultSet())->getFacetDistribution('unknown');
    }

    public function testFacetStatsAreIndexedByProperty(): void
    {
        $price = new FacetStat('price', 10, 100, null, null);

        $resultSet = (new ResultSet())->setFacetStats([$price]);

        $this->assertSame(['price'], array_keys($resultSet->getFacetStats()));
        $this->assertSame($price, $resultSet->getFacetStat('price'));
    }

    public function testUnknownFacetStatThrows(): void
    {
        $this->expectException(ResultSetException::class);

        (new ResultSet())->getFacetStat('unknown');
    }

    public function testSettersReplacePreviousValues(): void
    {
        $resultSet = (new ResultSet())
            ->setFacetDistributions([(new FacetTermDistribution())->setProperty('category')])
            ->setFacetDistributions([(new FacetTermDistribution())->setProperty('brand')]);

        $this->assertSame(['brand'], array_keys($resultSet->getFacetDistributions()));
    }
}
