---
name: ux-search
description: Build, customize or debug a search page powered by mezcalito/ux-search (Symfony UX faceted search with Algolia, Meilisearch or Doctrine adapters). Use when creating or editing a class with the #[AsSearch] attribute, configuring facets, sorts, hits per page, adapter parameters, URL rewriting or pre/post search events, overriding the Mezcalito:UxSearch Twig components, or diagnosing an empty, unknown or misbehaving search.
---

# Mezcalito UX Search

Symfony bundle providing faceted search as Twig and Live Components. A search is a PHP class
extending `Mezcalito\UxSearchBundle\Search\AbstractSearch`, tagged by the `#[AsSearch]` attribute,
and rendered with `<twig:Mezcalito:UxSearch:Layout name="<search name>" />`.

## Configuration

```yaml
# config/packages/mezcalito_ux_search.yaml
mezcalito_ux_search:
    default_adapter: default
    adapters:
        default: '%env(MEZCALITO_UX_SEARCH_DEFAULT_DSN)%'
```

DSN prefixes: `algolia://API_KEY@APP_ID`, `meilisearch://MASTER_KEY@host:7700`, `doctrine://<entity manager>`.
Several adapters can be declared and chosen per search with `#[AsSearch(adapter: '<name>')]`.
Required client packages: `algolia/algoliasearch-client-php`, `meilisearch/meilisearch-php` + `nyholm/psr7`,
or `doctrine/doctrine-bundle`.

## Creating a search

Generate the skeleton with `php bin/console make:search` (needs `symfony/maker-bundle`), or write it:

```php
#[AsSearch('products', name: 'products', adapter: 'meilisearch')]
final class ProductSearch extends AbstractSearch
{
    public function build(array $options = []): void
    {
        $this
            ->addFacet('brand', 'Brand')
            ->addFacet('price', 'Price', RangeSlider::class)
            ->addAvailableSort(null, 'Relevance')
            ->addAvailableSort('price:asc', 'Price ascending')
            ->setAvailableHitsPerPage([12, 24, 48])
            ->enableUrlRewriting()
        ;
    }
}
```

- `index`: index name (Algolia, Meilisearch) or entity FQCN (Doctrine).
- `name`: defaults to the lowercased short class name without `Search` (`ProductSearch` -> `product`).
  This is the value passed to `Layout name="..."`.
- `adapter`: key under `mezcalito_ux_search.adapters`, defaults to `default_adapter`.

### Facets

`addFacet(string $property, string $label, ?string $displayComponent = null, array $props = [])`.
Display components live in `Mezcalito\UxSearchBundle\Twig\Components\Facet\`: `RefinementList` (default,
term filter), `RangeInput` and `RangeSlider` (range filter, numeric property). The property must be
filterable/facetable on the engine side (Meilisearch `filterableAttributes`, Algolia `attributesForFaceting`).
With Doctrine, prefix the property with the query builder alias (`p.brand`).

### Sorts

| Adapter     | Key format                                   |
|-------------|----------------------------------------------|
| Meilisearch | `attribute:direction`, attribute must be sortable |
| Algolia     | replica index name                           |
| Doctrine    | `alias.attribute:direction`                  |

`null` key means engine default (relevance).

### Adapter parameters

`setAdapterParameters([...])` using the constants of the adapter class:

- `AlgoliaAdapter::*_PARAM` (highlight, retrieve, typo, maxValuesPerFacet, ...)
- `MeilisearchAdapter::*_PARAM` (attributesToRetrieve/Crop/Highlight, cropLength, distinct, ...)
- `DoctrineAdapter::SEARCH_FIELDS`, `QUERY_BUILDER_ALIAS`, `QUERY_BUILDER` (callable receiving the `QueryBuilder`
  for base conditions), `MAX_FACET_VALUES_PARAM`

Unknown keys are rejected by an `OptionsResolver`.

### Events

Inside `build()`: `addEventListener(PreSearchEvent::class, ...)` to alter the `Query` before execution,
`addEventListener(PostSearchEvent::class, ...)` to alter the `ResultSet` (enrich hits with `$hit->setData()`),
or `addEventSubscriber()`. A search class is a regular autowired service: inject dependencies in its constructor.

### URL rewriting

`enableUrlRewriting()` uses `DefaultUrlFormater`: `query`, `sortBy`, `page`, `<property>=a~~b`,
`<property>Min` / `<property>Max`, dots in properties become underscores. For custom URLs implement
`Mezcalito\UxSearchBundle\Search\Url\UrlFormaterInterface` (`generateUrl()` and `applyFilters()`,
both receiving a `CurrentRequest` with `route` and `parameters`) and select it with `setUrlFormater(MyFormater::class)`.

## UI

Components: `Layout` (LiveComponent, state holder), `SearchInput`, `Hits`, `TotalHits`, `Pagination`,
`SortBy`, `HitsPerPage`, `CurrentRefinements`, `ClearRefinements`, `Facet`, `Facet:RefinementList`,
`Facet:RangeInput`, `Facet:RangeSlider`. All are prefixed `Mezcalito:UxSearch:` and must be rendered inside `Layout`.

Override a template by creating the same path under `templates/components/Mezcalito/UxSearch/`
(e.g. `Hits.html.twig`, `Facet/RefinementList.html.twig`). The usual customization is the `hit` block of
`Hits.html.twig`; hits are iterated through `this.resultSet.hits`. To restructure the page, embed `Layout`
and override its blocks: `content`, `form`, `toolbar`, `facets`, `listing`, `stats`, `hits`, `pagination`.

## Debugging with Mate

1. Search not found / wrong search rendered: list registered searches and their tag attributes.
   ```
   vendor/bin/mate tools:call symfony-services --tag=mezcalito_ux_search.search
   vendor/bin/mate tools:call symfony-service-detail --id='App\Search\ProductSearch'
   ```
   The `mezcalito_ux_search.search` tag carries `alias` (the search name used by `Layout name`), `index`
   and `adapter`. A missing class means it is not autoconfigured (outside the `App\` resource, excluded,
   or missing `#[AsSearch]`).
2. Adapter not supported for a DSN: check the factories with
   `vendor/bin/mate tools:call symfony-services --tag=mezcalito_ux_search.adapter_factory` and that the client
   package of that engine is installed (`composer show algolia/algoliasearch-client-php`, etc.).
3. Custom URL formatter ignored: it must appear under `--tag=mezcalito_ux_search.url_formater`.
4. Engine errors (bad filter, non sortable attribute, unreachable host): the bundle logs on the
   `mezcalito_ux_search` Monolog channel.
   ```
   vendor/bin/mate tools:call monolog-search --term= --channel=mezcalito_ux_search
   ```
5. Live updates broken (facet clicks do nothing): inspect the `_live_component` sub-requests in the
   profiler (`symfony-profiler-debugging` skill) and check the response of the `Layout` component.

## Rules

- Never rename the `Layout` LiveProps or remove its `data-controller="ux-search"` wrapper in an override:
  the Stimulus controller and live actions depend on them.
- Keep facet properties, sort keys and URL parameters in sync with the engine configuration; the bundle does
  not create indexes or settings.
- Do not instantiate search classes manually; resolve them through `SearchProvider` or the `Layout` component.
  Outside `Layout`, call `$searchProvider->getSearch('<name>')->create()` before `Searcher::search()`:
  `create()` runs `build()` and sets up the event dispatcher.
