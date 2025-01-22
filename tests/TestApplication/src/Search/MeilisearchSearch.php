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

namespace Mezcalito\UxSearchBundle\Tests\TestApplication\Search;

use Mezcalito\UxSearchBundle\Adapter\Meilisearch\MeilisearchAdapter;
use Mezcalito\UxSearchBundle\Attribute\AsSearch;
use Mezcalito\UxSearchBundle\Event\PostSearchEvent;
use Mezcalito\UxSearchBundle\Search\AbstractSearch;
use Mezcalito\UxSearchBundle\Twig\Components\Facet\RangeSlider;

#[AsSearch('products', name: 'meilisearch', adapter: 'meilisearch')]
class MeilisearchSearch extends AbstractSearch
{
    public function build(array $options = []): void
    {
        $this
            ->setAdapterParameters([
                MeilisearchAdapter::CROP_LENGTH_PARAM => 5,
                MeilisearchAdapter::CROP_MARKER_PARAM => '$$$',
                MeilisearchAdapter::ATTRIBUTES_TO_HIGHLIGHT_PARAM => ['description'],
                MeilisearchAdapter::HIGHLIGHT_PRE_TAG_PARAM => '<strong>',
                MeilisearchAdapter::HIGHLIGHT_POST_TAG_PARAM => '</strong>',
            ])
            ->addFacet('type', 'Type', null, ['limit' => 2])
            ->addFacet('brand', 'Brand')
            ->addFacet('rating', 'Rating')
            ->addFacet('price_range', 'Price range')
            ->addFacet('price', 'Price', RangeSlider::class)
            ->setAvailableHitsPerPage([3, 6, 12])
            ->addAvailableSort(null, 'Relevancy')
            ->addAvailableSort('price:asc', 'Price ↑')
            ->addAvailableSort('price:desc', 'Price ↓')
            ->addAvailableSort('popularity:asc', 'Popularity ↑')
            ->addAvailableSort('popularity:desc', 'Popularity ↓')
            ->addEventListener(PostSearchEvent::class, function (PostSearchEvent $event) {
                foreach ($event->getResultSet()->getHits() as $hit) {
                    $data = $hit->getData();
                    $data['name'] .= ' - POST Update';
                    $hit->setData($data);
                }
            }, 2)
            ->enableUrlRewriting()
        ;
    }
}
