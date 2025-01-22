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
use Mezcalito\UxSearchBundle\Search\Facet;
use Mezcalito\UxSearchBundle\Search\Url\DefaultUrlFormater;
use Mezcalito\UxSearchBundle\Tests\Fixtures\Attribute\TestClass;
use PHPUnit\Framework\TestCase;

class AbstractSearchTest extends TestCase
{
    private AbstractSearch $search;

    protected function setUp(): void
    {
        $this->search = new class extends AbstractSearch {};
    }

    public function testCreate(): void
    {
        $result = $this->search->create();
        $this->assertInstanceOf(AbstractSearch::class, $result);
    }

    public function testGetIndexName(): void
    {
        $this->assertNull($this->search->getIndexName());
    }

    public function testGetAdapterName(): void
    {
        $this->assertNull($this->search->getAdapterName());
    }

    public function testAttribute(): void
    {
        $classWithAttribute = new TestClass();
        $this->assertSame('search_adapter', $classWithAttribute->getAdapterName());
        $this->assertSame('search_index', $classWithAttribute->getIndexName());
    }

    public function testSetAndGetAvailableHitsPerPage(): void
    {
        $hitsPerPage = [10, 20, 30];
        $this->search->setAvailableHitsPerPage($hitsPerPage);

        $this->assertSame($hitsPerPage, $this->search->getAvailableHitsPerPage());
    }

    public function testAddAndGetAvailableSorts(): void
    {
        $this->search->addAvailableSort('default_sort', 'asc');
        $this->assertCount(1, $this->search->getAvailableSorts());
    }

    public function testAddFacetAddsFacetToArray(): void
    {
        $this->search->addFacet('category', 'Category', 'Dropdown', ['option1' => 'value1']);

        $this->assertCount(1, $this->search->getFacets());
        $facet = $this->search->getFacets()[0];

        $this->assertInstanceOf(Facet::class, $facet);
        $this->assertSame('category', $facet->getProperty());
        $this->assertSame('Category', $facet->getLabel());
        $this->assertSame('Dropdown', $facet->getDisplayComponent());
        $this->assertSame(['option1' => 'value1'], $facet->getProps());
    }

    public function testGetFacet(): void
    {
        $this->search->addFacet('test_property', 'label');

        $this->assertSame('test_property', $this->search->getFacet('test_property')->getProperty());
        $this->assertSame('label', $this->search->getFacet('test_property')->getLabel());
    }

    public function testGetFacetThrowsException(): void
    {
        $this->expectException(SearchException::class);
        $this->search->getFacet('non_existing_property');
    }

    public function testSetAndGetAdapterParameters(): void
    {
        $parameters = [
            'param1' => 'value1',
            'param2' => 'value2',
        ];

        $this->search->setAdapterParameters($parameters);

        $this->assertSame($parameters, $this->search->getAdapterParameters());
    }

    public function testSetAndGetResolvedAdapterParameters(): void
    {
        $parameters = [
            'param1' => 'value1',
            'param2' => 'value2',
        ];

        $this->search->setResolvedAdapterParameters($parameters);

        $this->assertSame($parameters, $this->search->getResolvedAdapterParameters());
    }

    public function testCreateQuery(): void
    {
        $query = $this->search->createQuery();
        $this->assertNotNull($query);
        $this->assertSame(12, $query->getActiveHitsPerPage());

        $this->search->addAvailableSort('default_sort', 'asc');

        $query = $this->search->createQuery();
        $this->assertSame('default_sort', $query->getActiveSort());
    }

    public function testEnableUrlRewriting(): void
    {
        $this->assertFalse($this->search->hasUrlRewriting());

        $this->search->enableUrlRewriting();
        $this->assertTrue($this->search->hasUrlRewriting());
    }

    public function testGetUrlFormaterReturnsDefault(): void
    {
        $this->assertSame(DefaultUrlFormater::class, $this->search->getUrlFormater());
    }

    public function testSetUrlFormater(): void
    {
        $customUrlFormater = 'App\\CustomUrlFormater';

        $this->search->setUrlFormater($customUrlFormater);
        $this->assertSame($customUrlFormater, $this->search->getUrlFormater());
    }
}
