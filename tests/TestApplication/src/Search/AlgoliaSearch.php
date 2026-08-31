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

use Mezcalito\UxSearchBundle\Adapter\Algolia\AlgoliaAdapter;
use Mezcalito\UxSearchBundle\Attribute\AsSearch;
use Mezcalito\UxSearchBundle\Event\PostSearchEvent;
use Mezcalito\UxSearchBundle\Search\AbstractSearch;
use Mezcalito\UxSearchBundle\Twig\Components\Facet\RangeSlider;

#[AsSearch('instant_search', name: 'algolia', adapter: 'algolia')]
class AlgoliaSearch extends AbstractSearch
{
    public function build(array $options = []): void
    {
        $this
            ->setAdapterParameters([
                AlgoliaAdapter::ATTRIBUTES_TO_HIGHLIGHT_PARAM => ['description'],
                AlgoliaAdapter::HIGHLIGHT_PRE_TAG_PARAM => '<strong>',
                AlgoliaAdapter::HIGHLIGHT_POST_TAG_PARAM => '</strong>',
            ])
            ->addFacet('type', 'Type', null, ['limit' => 2, 'enableSearch' => true, 'enableSort' => true])
            ->addFacet('brand', 'Brand', props: ['enableSearch' => true, 'enableSort' => true])
            ->addFacet('rating', 'Rating')
            ->addFacet('price_range', 'Price range')
            ->addFacet('price', 'Price', RangeSlider::class)
            ->addAvailableSort('instant_search', 'Default')
            ->addAvailableSort('instant_search_price_asc', 'Price ↑')
            ->addAvailableSort('instant_search_price_desc', 'Price ↓')
            ->addEventListener(PostSearchEvent::class, static function (PostSearchEvent $event) {
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
