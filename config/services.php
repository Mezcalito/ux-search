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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\Persistence\ManagerRegistry;
use Mezcalito\UxSearchBundle\Adapter\AdapterProvider;
use Mezcalito\UxSearchBundle\Adapter\Doctrine\DoctrineFactory;
use Mezcalito\UxSearchBundle\Adapter\Meilisearch\MeilisearchFactory;
use Mezcalito\UxSearchBundle\Adapter\Meilisearch\QueryBuilder;
use Mezcalito\UxSearchBundle\Context\ContextProvider;
use Mezcalito\UxSearchBundle\EventSubscriber\ContextSubscriber;
use Mezcalito\UxSearchBundle\Maker\MakeSearch;
use Mezcalito\UxSearchBundle\Search\Searcher;
use Mezcalito\UxSearchBundle\Search\SearchProvider;
use Mezcalito\UxSearchBundle\Search\Url\DefaultUrlFormater;
use Mezcalito\UxSearchBundle\Search\Url\UrlFormaterProvider;
use Mezcalito\UxSearchBundle\Twig\Components\ClearRefinements;
use Mezcalito\UxSearchBundle\Twig\Components\CurrentRefinements;
use Mezcalito\UxSearchBundle\Twig\Components\Facet;
use Mezcalito\UxSearchBundle\Twig\Components\Hits;
use Mezcalito\UxSearchBundle\Twig\Components\HitsPerPage;
use Mezcalito\UxSearchBundle\Twig\Components\Layout;
use Mezcalito\UxSearchBundle\Twig\Components\Pagination;
use Mezcalito\UxSearchBundle\Twig\Components\SearchInput;
use Mezcalito\UxSearchBundle\Twig\Components\SortBy;
use Mezcalito\UxSearchBundle\Twig\Components\TotalHits;
use Mezcalito\UxSearchBundle\Twig\UxSearchExtension;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\LiveComponent\LiveResponder;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set(DoctrineFactory::class)
            ->arg('$managerRegistry', service(ManagerRegistry::class)->nullOnInvalid())
            ->tag('mezcalito_ux_search.adapter_factory')
        ->set(MeilisearchFactory::class)->tag('mezcalito_ux_search.adapter_factory')
        ->set(Searcher::class)
            ->arg('$adapterProvider', service(AdapterProvider::class))
            ->arg('$contextProvider', service(ContextProvider::class))
        ->set(QueryBuilder::class)
        ->set(ContextProvider::class)
        ->set(AdapterProvider::class)
            ->arg('$defaultAdapterName', param('mezcalito_ux_search.default_adapter'))
            ->arg('$adapterConfiguration', param('mezcalito_ux_search.adapters'))
        ->set(SearchProvider::class)
        ->set(UrlFormaterProvider::class)
        ->set(Layout::class)
            ->arg('$searchConfigurationProvider', service(SearchProvider::class))
            ->arg('$searcher', service(Searcher::class))
            ->arg('$requestStack', service(RequestStack::class))
            ->arg('$urlFormaterProvider', service(UrlFormaterProvider::class))
            ->call('setLiveResponder', [service(LiveResponder::class)])
            ->tag('twig.component', [
                'key' => 'Mezcalito:UxSearch:Layout',
                'expose_public_props' => true,
                'attributes_var' => 'attributes',
                'live' => true,
                'csrf' => true,
                'route' => 'ux_live_component',
                'method' => 'post',
                'url_reference_type' => true,
            ])
            ->tag('controller.service_arguments')
            ->public()
        ->set(Hits::class)
            ->arg('$contextProvider', service(ContextProvider::class))
            ->tag('twig.component', ['key' => 'Mezcalito:UxSearch:Hits'])
        ->set(TotalHits::class)
            ->arg('$contextProvider', service(ContextProvider::class))
            ->tag('twig.component', ['key' => 'Mezcalito:UxSearch:TotalHits'])
        ->set(SortBy::class)
            ->arg('$contextProvider', service(ContextProvider::class))
            ->tag('twig.component', ['key' => 'Mezcalito:UxSearch:SortBy'])
        ->set(HitsPerPage::class)
            ->arg('$contextProvider', service(ContextProvider::class))
            ->tag('twig.component', ['key' => 'Mezcalito:UxSearch:HitsPerPage'])
        ->set(Pagination::class)
            ->arg('$contextProvider', service(ContextProvider::class))
            ->tag('twig.component', [
                'key' => 'Mezcalito:UxSearch:Pagination',
                'expose_public_props' => true,
            ])
        ->set(Facet::class)
            ->arg('$contextProvider', service(ContextProvider::class))
            ->tag('twig.component', [
                'key' => 'Mezcalito:UxSearch:Facet',
                'expose_public_props' => true,
            ])
        ->set(Facet\RefinementList::class)
            ->arg('$contextProvider', service(ContextProvider::class))
            ->tag('twig.component', ['key' => 'Mezcalito:UxSearch:Facet:RefinementList'])
        ->set(Facet\RangeInput::class)
            ->arg('$contextProvider', service(ContextProvider::class))
            ->tag('twig.component', ['key' => 'Mezcalito:UxSearch:Facet:RangeInput'])
        ->set(Facet\RangeSlider::class)
            ->arg('$contextProvider', service(ContextProvider::class))
            ->tag('twig.component', ['key' => 'Mezcalito:UxSearch:Facet:RangeSlider'])
        ->set(CurrentRefinements::class)
            ->arg('$contextProvider', service(ContextProvider::class))
            ->tag('twig.component', ['key' => 'Mezcalito:UxSearch:CurrentRefinements'])
        ->set(ClearRefinements::class)
            ->arg('$contextProvider', service(ContextProvider::class))
            ->tag('twig.component', ['key' => 'Mezcalito:UxSearch:ClearRefinements'])
        ->set(SearchInput::class)
            ->tag('twig.component', ['key' => 'Mezcalito:UxSearch:SearchInput'])
        ->set(ContextSubscriber::class)
            ->arg('$contextProvider', service(ContextProvider::class))
        ->set(UxSearchExtension::class)->tag('twig.extension')
        ->set(DefaultUrlFormater::class)
            ->arg('$urlGenerator', service(UrlGeneratorInterface::class))
            ->tag('mezcalito_ux_search.url_formater')
        ->set('maker.maker.make_search', MakeSearch::class)
            ->tag('maker.command')
    ;
};
