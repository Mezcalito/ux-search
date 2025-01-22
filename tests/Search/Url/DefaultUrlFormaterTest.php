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

namespace Mezcalito\UxSearchBundle\Tests\Search\Url;

use Mezcalito\UxSearchBundle\Search\Facet;
use Mezcalito\UxSearchBundle\Search\Filter\RangeFilter;
use Mezcalito\UxSearchBundle\Search\Filter\TermFilter;
use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\SearchInterface;
use Mezcalito\UxSearchBundle\Search\Url\CurrentRequest;
use Mezcalito\UxSearchBundle\Search\Url\DefaultUrlFormater;
use Mezcalito\UxSearchBundle\Twig\Components\Facet\RangeInput;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class DefaultUrlFormaterTest extends TestCase
{
    public function testGenerateUrl(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $formater = new DefaultUrlFormater($urlGenerator);

        $currentRequest = new CurrentRequest('search_route', ['query' => 'test', 'sortBy' => 'price_asc', 'page' => 1]);
        $query = new Query();
        $query->setQueryString('test');
        $query->setCurrentPage(2)->setActiveSort('price_desc');
        $query->addActiveFilter(new TermFilter('category', ['books', 'electronics']));
        $query->addActiveFilter(new RangeFilter('price', 10.5, 99.9));
        $query->addActiveFilter(new TermFilter('o_type', ['accessories']));

        $search = $this->createMock(SearchInterface::class);
        $search->method('getFacets')->willReturn([]);

        $urlGenerator->expects($this->once())
            ->method('generate')
            ->with(
                'search_route',
                [
                    'query' => 'test',
                    'sortBy' => 'price_desc',
                    'page' => 2,
                    'category' => 'books~~electronics',
                    'priceMin' => 10.5,
                    'priceMax' => 99.9,
                    'o_type' => 'accessories',
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('https://example.com/search?sortBy=price_desc&page=2&category=books~~electronics&priceMin=10.5&priceMax=99.9&o_type=accessories')
        ;

        $result = $formater->generateUrl($currentRequest, $search, $query);

        $this->assertSame('https://example.com/search?sortBy=price_desc&page=2&category=books~~electronics&priceMin=10.5&priceMax=99.9&o_type=accessories', $result);
    }

    public function testGenerateUrlWithoutParams(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $formater = new DefaultUrlFormater($urlGenerator);

        $currentRequest = new CurrentRequest('search_route', ['other_param' => 'test']);
        $query = new Query();
        $query->setCurrentPage(1);

        $search = $this->createMock(SearchInterface::class);
        $search->method('getFacets')->willReturn([]);

        $urlGenerator->expects($this->once())
            ->method('generate')
            ->with(
                'search_route',
                ['other_param' => 'test'],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('https://example.com/search?other_param=test')
        ;

        $result = $formater->generateUrl($currentRequest, $search, $query);

        $this->assertSame('https://example.com/search?other_param=test', $result);
    }

    public function testApplyFilters(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $formater = new DefaultUrlFormater($urlGenerator);

        $currentRequest = new CurrentRequest('search_route', [
            'query' => 'test',
            'page' => '3',
            'sortBy' => 'popularity',
            'category' => 'music~~movies',
            'priceMin' => '20',
            'priceMax' => '200',
            'o_type' => 'accessories',
            'o_popularityMin' => '0',
            'o_popularityMax' => '5',
        ]);

        $query = new Query();
        $search = $this->createMock(SearchInterface::class);
        $search->method('getFacets')->willReturn([
            new Facet('category', 'category'),
            new Facet('price', 'price', RangeInput::class),
            new Facet('o.type', 'accessories'),
            new Facet('o.popularity', 'popularity', RangeInput::class),
        ]);

        $formater->applyFilters($currentRequest, $search, $query);

        $this->assertSame('test', $query->getQueryString());

        $this->assertSame(3, $query->getCurrentPage());
        $this->assertSame('popularity', $query->getActiveSort());

        $filters = $query->getActiveFilters();
        $this->assertCount(4, $filters);

        /** @var TermFilter $termFilter */
        $termFilter = $filters[0];
        $this->assertInstanceOf(TermFilter::class, $termFilter);
        $this->assertSame('category', $termFilter->getProperty());
        $this->assertSame(['music', 'movies'], $termFilter->getValues());

        /** @var RangeFilter $rangeFilter */
        $rangeFilter = $filters[1];
        $this->assertInstanceOf(RangeFilter::class, $rangeFilter);
        $this->assertSame('price', $rangeFilter->getProperty());
        $this->assertSame(20.0, $rangeFilter->getMin());
        $this->assertSame(200.0, $rangeFilter->getMax());

        /** @var TermFilter $termFilter */
        $termFilter = $filters[2];
        $this->assertInstanceOf(TermFilter::class, $termFilter);
        $this->assertSame('o.type', $termFilter->getProperty());
        $this->assertSame(['accessories'], $termFilter->getValues());

        /** @var RangeFilter $rangeFilter */
        $rangeFilter = $filters[3];
        $this->assertInstanceOf(RangeFilter::class, $rangeFilter);
        $this->assertSame('o.popularity', $rangeFilter->getProperty());
        $this->assertSame(0.0, $rangeFilter->getMin());
        $this->assertSame(5.0, $rangeFilter->getMax());
    }

    public function testClearParameters(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $formater = new DefaultUrlFormater($urlGenerator);

        $currentRequest = new CurrentRequest('search_route', [
            'page' => 2,
            'sortBy' => 'price_asc',
            'extraParam' => 'value',
        ]);

        $search = $this->createMock(SearchInterface::class);
        $search->method('getFacets')->willReturn([
            new Facet('category', 'category'),
        ]);

        $query = new Query();
        $query->setCurrentPage(2)->setActiveSort('price_asc');

        $urlGenerator->expects($this->once())
            ->method('generate')
            ->with(
                'search_route',
                ['extraParam' => 'value', 'sortBy' => 'price_asc', 'page' => 2],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

        $formater->generateUrl($currentRequest, $search, $query);
    }
}
