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

namespace Mezcalito\UxSearchBundle\Tests\Adapter\Meilisearch;

use Meilisearch\Client;
use Mezcalito\UxSearchBundle\Adapter\Meilisearch\MeilisearchAdapter;
use Mezcalito\UxSearchBundle\Adapter\Meilisearch\QueryBuilder;
use Mezcalito\UxSearchBundle\Exception\ResultSetException;
use Mezcalito\UxSearchBundle\Search\Facet;
use Mezcalito\UxSearchBundle\Search\Filter\RangeFilter;
use Mezcalito\UxSearchBundle\Search\Filter\TermFilter;
use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\ResultSet\FacetStat;
use Mezcalito\UxSearchBundle\Search\ResultSet\FacetTermDistribution;
use Mezcalito\UxSearchBundle\Search\ResultSet\Hit;
use Mezcalito\UxSearchBundle\Search\ResultSet\ResultSet;
use Mezcalito\UxSearchBundle\Search\SearchInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeilisearchAdapterTest extends TestCase
{
    private MeilisearchAdapter $meilisearchAdapter;

    private Client $client;

    private SearchInterface $search;

    protected function setUp(): void
    {
        $this->client = $this->createStub(Client::class);
        $this->meilisearchAdapter = new MeilisearchAdapter($this->client, new QueryBuilder());
        $this->search = $this->createStub(SearchInterface::class);
        $this->search->method('getIndexName')->willReturn('test');
        $this->search->method('getFacets')->willReturn([
            new Facet('color', 'Color'),
            new Facet('price', 'Price'),
        ]);
        $this->search->method('getResolvedAdapterParameters')->willReturn([
            'attributesToRetrieve' => ['*'],
            'attributesToCrop' => [],
            'cropLength' => 10,
            'cropMarker' => '...',
            'attributesToHighlight' => [],
            'highlightPreTag' => '<em>',
            'highlightPostTag' => '</em>',
        ]);
    }

    public function testConfigureParametersWithInvalidType(): void
    {
        $this->expectException(InvalidOptionsException::class);

        $searchMock = $this->createMock(SearchInterface::class);

        $invalidParameters = [
            'attributesToRetrieve' => 'invalid_string',
        ];

        $searchMock->method('getAdapterParameters')->willReturn($invalidParameters);

        $adapter = new MeilisearchAdapter(
            $this->createMock(Client::class),
            $this->createMock(QueryBuilder::class)
        );

        $resolver = new OptionsResolver();
        $adapter->configureParameters($resolver);
        $resolver->resolve($invalidParameters);
    }

    public function testWithoutFilter(): void
    {
        $query = new Query();

        $response = file_get_contents(__DIR__.'/../../Fixtures/Adapter/Meilisearch/result-simple.json');
        $this->client->method('multiSearch')
            ->willReturn(json_decode($response, true));

        $resultSet = $this->meilisearchAdapter->search($query, $this->search);

        // ResultSet
        $this->assertInstanceOf(ResultSet::class, $resultSet);
        $this->assertCount(12, $resultSet->getHits());
        $this->assertEquals('test', $resultSet->getIndexUid());
        $this->assertEquals(120, $resultSet->getTotalResults());
        $this->assertCount(2, $resultSet->getFacetDistributions());
        $this->assertCount(2, $resultSet->getFacetStats());

        // Hit
        $firstHit = $resultSet->getHits()[0];
        $this->assertInstanceOf(Hit::class, $firstHit);
        $this->assertEquals(1.0, $firstHit->getScore());
        $this->assertIsArray($firstHit->getData());

        // Term Distribution
        $colorDistribution = $resultSet->getFacetDistribution('color');
        $this->assertInstanceOf(FacetTermDistribution::class, $colorDistribution);
        $this->assertCount(5, $colorDistribution->getValues());
        $this->assertCount(0, $colorDistribution->getCheckedValues());

        // FacetStat
        $priceStat = $resultSet->getFacetStat('price');
        $this->assertInstanceOf(FacetStat::class, $priceStat);
        $this->assertEquals(5.5, $priceStat->getMin());
        $this->assertEquals(9.8, $priceStat->getMax());
        $this->assertNull($priceStat->getUserMin());
        $this->assertNull($priceStat->getUserMax());

        $this->expectException(ResultSetException::class);
        $resultSet->getFacetStat('missing');
    }

    public function testWithFilter(): void
    {
        $query = (new Query())
            ->addActiveFilter((new TermFilter('color'))->setValues(['Blonde', 'Amber']))
            ->addActiveFilter((new RangeFilter('price'))->setMin(5)->setMax(10))
        ;

        $response = file_get_contents(__DIR__.'/../../Fixtures/Adapter/Meilisearch/result-with-filters.json');
        $this->client->method('multiSearch')
            ->willReturn(json_decode($response, true));

        $resultSet = $this->meilisearchAdapter->search($query, $this->search);

        // ResultSet
        $this->assertInstanceOf(ResultSet::class, $resultSet);
        $this->assertCount(12, $resultSet->getHits());
        $this->assertEquals('test', $resultSet->getIndexUid());
        $this->assertEquals(61, $resultSet->getTotalResults());
        $this->assertCount(2, $resultSet->getFacetDistributions());

        // Term Distribution
        $colorDistribution = $resultSet->getFacetDistribution('color');
        $this->assertInstanceOf(FacetTermDistribution::class, $colorDistribution);
        $this->assertCount(4, $colorDistribution->getValues());
        $this->assertCount(2, $colorDistribution->getCheckedValues());
        $this->assertTrue($colorDistribution->isChecked('Amber'));
        $this->assertTrue($colorDistribution->isChecked('Blonde'));
        $this->assertFalse($colorDistribution->isChecked('Red'));

        // FacetStat
        $priceStat = $resultSet->getFacetStat('price');
        $this->assertInstanceOf(FacetStat::class, $priceStat);
        $this->assertEquals(5.5, $priceStat->getMin());
        $this->assertEquals(7.8, $priceStat->getMax());
        $this->assertEquals(5, $priceStat->getUserMin());
        $this->assertEquals(10, $priceStat->getUserMax());

        $this->expectException(ResultSetException::class);
        $resultSet->getFacetDistribution('missing');
    }
}
