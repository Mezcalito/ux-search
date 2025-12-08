# Customize Your Search

> [!IMPORTANT]
> If you haven't used the maker, you first need to create a class and add the `AsSearch` attribute to it.

## Creating a Search Class

```php
<?php

declare(strict_types=1);

namespace App\Search;

use Mezcalito\UxSearchBundle\Attribute\AsSearch;
use Mezcalito\UxSearchBundle\Search\AbstractSearch;

#[AsSearch('products')]
class ProductSearch extends AbstractSearch
{
    public function build(array $options = []): void
    {
        // Configure your search here
    }
}
```

The `#[AsSearch]` attribute takes the following parameters:

| Parameter | Description                                                               | Required |
|-----------|---------------------------------------------------------------------------|----------|
| index     | Index/Entity name (index name for Algolia/Meilisearch, FQCN for Doctrine) | ✅        |
| name      | Custom search name (defaults to class name without "Search" suffix)       | ❌        |
| adapter   | Adapter name from configuration (defaults to `default_adapter`)           | ❌        |

**Examples:**

```php
// Algolia with custom name
#[AsSearch('products_index', name: 'products', adapter: 'algolia')]

// Meilisearch
#[AsSearch('products', adapter: 'meilisearch')]

// Doctrine ORM
#[AsSearch(Product::class, adapter: 'doctrine')]
```

---

## Add Facets

Facets allow users to filter search results. Use the `addFacet()` method to configure them.

### Method Signature

```php
addFacet(
    string $property,          // Property name
    string $label,             // Display label
    ?string $displayComponent = null,  // Custom component FQCN
    array $props = []          // Props for component
): static
```

### Parameters

| Parameter        | Description                 | Type   | Required |
|------------------|-----------------------------|--------|----------|
| property         | Property name (e.g., 'brand', 'price') | string | ✅ |
| label            | Label displayed in UI       | string | ✅ |
| displayComponent | FQCN of Twig component      | string | ❌ |
| props            | Props to pass to component  | array  | ❌ |

### Default Facet Types

The bundle provides three facet component types:

- **RefinementList** (default) - Checkbox list for discrete values
- **RangeSlider** - Slider for numeric ranges
- **RangeInput** - Min/max inputs for numeric ranges

### Examples

```php
use Mezcalito\UxSearchBundle\Twig\Components\Facet\RangeInput;
use Mezcalito\UxSearchBundle\Twig\Components\Facet\RangeSlider;

public function build(array $options = []): void
{
    $this
        // Default RefinementList facet
        ->addFacet('brand', 'Brand')

        // With custom component
        ->addFacet('price', 'Price', RangeSlider::class)
        ->addFacet('rating', 'Rating', RangeInput::class)

        // With props (limits displayed values)
        ->addFacet('category', 'Category', null, ['limit' => 10])
        ->addFacet('type', 'Type', null, ['limit' => 5, 'showMore' => true])
    ;
}
```

---

## Add Sorting

Allow users to sort results using `addAvailableSort()`.

### Method Signature

```php
addAvailableSort(
    ?string $key,    // Sort key (format: "attribute:direction")
    string $label    // Display label
): static
```

### Parameters

| Parameter | Description                                                                         | Required |
|-----------|-------------------------------------------------------------------------------------|----------|
| key       | Attribute and direction, separated by ':' (e.g., 'price:asc') or `null` for default | ✅        |
| label     | Label displayed in UI                                                               | ✅        |

**Note:** If `key` is `null` or empty, the adapter's default sorting is used (usually relevance).

### Format by Adapter

| Adapter         | Format                      | Example                                         |
|-----------------|-----------------------------|-------------------------------------------------|
| **Meilisearch** | `attribute:direction`       | `'price:asc'`, `'rating:desc'`                  |
| **Algolia**     | Index replica name          | `'products_price_asc'`, `'products_price_desc'` |
| **Doctrine**    | `alias.attribute:direction` | `'p.price:asc'`, `'p.createdAt:desc'`           |

### Examples

```php
public function build(array $options = []): void
{
    $this
        // Default/relevance sort
        ->addAvailableSort(null, 'Relevance')

        // Meilisearch/Doctrine
        ->addAvailableSort('price:asc', 'Price: Low to High')
        ->addAvailableSort('price:desc', 'Price: High to Low')
        ->addAvailableSort('created_at:desc', 'Newest First')

        // Algolia (replica indexes)
        ->addAvailableSort('products_index', 'Relevance')
        ->addAvailableSort('products_index_price_asc', 'Price ↑')
        ->addAvailableSort('products_index_price_desc', 'Price ↓')
    ;
}
```

---

## Configure Hits Per Page

Control pagination options using `setAvailableHitsPerPage()`.

### Method Signature

```php
setAvailableHitsPerPage(array $availableHitsPerPage): static
```

### Parameters

| Parameter            | Description                                             | Type  | Default |
|----------------------|---------------------------------------------------------|-------|---------|
| availableHitsPerPage | Array of integers representing results per page options | int[] | `[12]`  |

### Example

```php
public function build(array $options = []): void
{
    $this
        // Users can choose 12, 24, 48, or 96 results per page
        ->setAvailableHitsPerPage([12, 24, 48, 96])
    ;
}
```

The first value becomes the default. In the above example, 12 results per page is the default.

---

## Set Adapter Parameters

Configure adapter-specific behavior using `setAdapterParameters()`.

### Method Signature

```php
setAdapterParameters(array $adapterParameters): static
```

### Examples by Adapter

#### Algolia

```php
use Mezcalito\UxSearchBundle\Adapter\Algolia\AlgoliaAdapter;

->setAdapterParameters([
    AlgoliaAdapter::ATTRIBUTES_TO_HIGHLIGHT_PARAM => ['name', 'description'],
    AlgoliaAdapter::HIGHLIGHT_PRE_TAG_PARAM => '<mark>',
    AlgoliaAdapter::HIGHLIGHT_POST_TAG_PARAM => '</mark>',
    AlgoliaAdapter::MAX_VALUES_PER_FACET_PARAM => 50,
])
```

See [Algolia documentation](algolia.md) for all available parameters.

#### Meilisearch

```php
use Mezcalito\UxSearchBundle\Adapter\Meilisearch\MeilisearchAdapter;

->setAdapterParameters([
    MeilisearchAdapter::ATTRIBUTES_TO_CROP_PARAM => ['description'],
    MeilisearchAdapter::CROP_LENGTH_PARAM => 20,
    MeilisearchAdapter::ATTRIBUTES_TO_HIGHLIGHT_PARAM => ['name'],
    MeilisearchAdapter::DISTINCT_PARAM => 'product_group_id',
])
```

See [Meilisearch documentation](meilisearch.md) for all available parameters.

#### Doctrine

```php
use Mezcalito\UxSearchBundle\Adapter\Doctrine\DoctrineAdapter;
use Doctrine\ORM\QueryBuilder;

->setAdapterParameters([
    DoctrineAdapter::SEARCH_FIELDS => ['p.name', 'p.description'],
    DoctrineAdapter::QUERY_BUILDER_ALIAS => 'p',
    DoctrineAdapter::QUERY_BUILDER => function (QueryBuilder $qb) {
        $qb->andWhere('p.active = :active')
           ->setParameter('active', true);
    },
])
```

See [Doctrine documentation](doctrine.md) for all available parameters.

---

## Event Listeners and Subscribers

Customize search behavior by listening to events.

### Available Events

#### PreSearchEvent

Dispatched **before** the search is executed. Use it to:
- Modify the query parameters
- Add filters programmatically
- Change search configuration based on user context
- Log search queries

```php
use Mezcalito\UxSearchBundle\Event\PreSearchEvent;

->addEventListener(PreSearchEvent::class, function (PreSearchEvent $event) {
    $query = $event->getQuery();
    $search = $event->getSearch();

    // Add automatic filter based on user role
    if ($this->security->isGranted('ROLE_PREMIUM')) {
        // Premium users see exclusive products
        $query->addFilter('exclusive', ['true']);
    }

    // Log the search query
    $this->logger->info('Search query', [
        'query' => $query->getQuery(),
        'user' => $this->security->getUser()?->getEmail(),
    ]);
})
```

#### PostSearchEvent

Dispatched **after** the search is executed. Use it to:
- Modify search results
- Enrich hits with database data
- Add computed fields
- Track analytics
- Transform data for display

```php
use Mezcalito\UxSearchBundle\Event\PostSearchEvent;

->addEventListener(PostSearchEvent::class, function (PostSearchEvent $event) {
    $resultSet = $event->getResultSet();
    $query = $event->getQuery();

    // Enrich hits with data from database
    foreach ($resultSet->getHits() as $hit) {
        $data = $hit->getData();

        // Add stock information
        $product = $this->productRepository->find($data['id']);
        $data['in_stock'] = $product->getStock() > 0;
        $data['stock_level'] = $product->getStock();

        // Add computed discount
        $data['discount_percent'] = $this->calculateDiscount($product);

        // Generate URL
        $data['url'] = $this->router->generate('product_show', [
            'slug' => $data['slug']
        ]);

        $hit->setData($data);
    }

    // Track search analytics
    $this->analytics->track('search', [
        'query' => $query->getQuery(),
        'results_count' => $resultSet->getTotalResults(),
    ]);
})
```

### Event Priority

You can specify priority when adding listeners (higher number = executed first):

```php
->addEventListener(PostSearchEvent::class, $listener1, priority: 10) // Executes first
->addEventListener(PostSearchEvent::class, $listener2, priority: 5)  // Executes second
->addEventListener(PostSearchEvent::class, $listener3, priority: 0)  // Executes last (default)
```

**Use cases for priority:**
- **High priority (10+)**: Data enrichment, critical transformations
- **Medium priority (5-9)**: Business logic, filtering
- **Low priority (0-4)**: Logging, analytics, non-critical tasks

### Event Subscribers

For complex event handling, use event subscribers:

```php
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SearchAnalyticsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AnalyticsService $analytics,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PreSearchEvent::class => ['onPreSearch', 10],
            PostSearchEvent::class => ['onPostSearch', 5],
        ];
    }

    public function onPreSearch(PreSearchEvent $event): void
    {
        $this->analytics->startTimer('search');
    }

    public function onPostSearch(PostSearchEvent $event): void
    {
        $duration = $this->analytics->endTimer('search');

        $this->analytics->track('search_completed', [
            'query' => $event->getQuery()->getQuery(),
            'results' => $event->getResultSet()->getTotalResults(),
            'duration_ms' => $duration,
        ]);
    }
}

// Register in your Search
->addEventSubscriber(new SearchAnalyticsSubscriber($this->analytics))
```

---

## URL Rewriting

Enable shareable search URLs with filters and parameters.

### Enable URL Rewriting

```php
public function build(array $options = []): void
{
    $this
        // ...other configuration
        ->enableUrlRewriting()
    ;
}
```

### Default URL Format

By default, `DefaultUrlFormater` generates URLs like:

```
/search?q=laptop&facets[brand][]=Dell&facets[brand][]=HP&facets[price][min]=500&facets[price][max]=1500&sort=price:asc&page=2
```

### Custom URL Formatter

Create a custom formatter to control URL structure:

#### 1. Implement UrlFormaterInterface

```php
<?php

namespace App\Search\Url;

use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\SearchInterface;
use Mezcalito\UxSearchBundle\Search\Url\UrlFormaterInterface;
use Symfony\Component\HttpFoundation\Request;

class CustomUrlFormater implements UrlFormaterInterface
{
    public function generateUrl(Query $query, SearchInterface $search): string
    {
        // Build clean URL from Query object
        $parts = [];

        if ($query->getQuery()) {
            $parts[] = 'q/' . urlencode($query->getQuery());
        }

        // Add filters
        foreach ($query->getFilters() as $filter) {
            $property = str_replace('.', '-', $filter->getProperty());
            $values = implode(',', $filter->getValues());
            $parts[] = $property . '/' . urlencode($values);
        }

        // Add numeric filters
        foreach ($query->getNumericFilters() as $filter) {
            $property = str_replace('.', '-', $filter->getProperty());
            $parts[] = $property . '/' . $filter->getMin() . '-' . $filter->getMax();
        }

        // Add sort
        if ($sort = $query->getActiveSort()) {
            $parts[] = 'sort/' . urlencode($sort);
        }

        // Add page
        if ($query->getPage() > 1) {
            $parts[] = 'page/' . $query->getPage();
        }

        return '/' . implode('/', $parts);
        // Example: /q/laptop/brand/Dell,HP/price/500-1500/sort/price:asc/page/2
    }

    public function applyFilters(Request $request, Query $query, SearchInterface $search): void
    {
        // Parse URL and apply filters to Query object
        $path = trim($request->getPathInfo(), '/');
        $segments = explode('/', $path);

        for ($i = 0; $i < count($segments); $i += 2) {
            $key = $segments[$i] ?? null;
            $value = $segments[$i + 1] ?? null;

            if (!$key || !$value) {
                continue;
            }

            match ($key) {
                'q' => $query->setQuery(urldecode($value)),
                'page' => $query->setPage((int) $value),
                'sort' => $query->setActiveSort(urldecode($value)),
                'brand', 'category', 'type' => $query->addFilter(
                    str_replace('-', '.', $key),
                    explode(',', urldecode($value))
                ),
                'price', 'rating' => (function () use ($query, $key, $value) {
                    [$min, $max] = explode('-', $value);
                    $query->addNumericFilter(
                        str_replace('-', '.', $key),
                        (float) $min,
                        (float) $max
                    );
                })(),
                default => null,
            };
        }
    }
}
```

#### 2. Register and Use Custom Formatter

```php
use App\Search\Url\CustomUrlFormater;

public function build(array $options = []): void
{
    $this
        // ...other configuration
        ->enableUrlRewriting()
        ->setUrlFormater(CustomUrlFormater::class)
    ;
}
```

Your searches will now generate and parse clean, SEO-friendly URLs.

---

## Complete Example

Here's a fully configured Search class demonstrating all features:

```php
<?php

declare(strict_types=1);

namespace App\Search;

use App\Entity\Product;
use App\EventSubscriber\SearchAnalyticsSubscriber;
use Doctrine\ORM\QueryBuilder;
use Mezcalito\UxSearchBundle\Adapter\Doctrine\DoctrineAdapter;
use Mezcalito\UxSearchBundle\Attribute\AsSearch;
use Mezcalito\UxSearchBundle\Event\PostSearchEvent;
use Mezcalito\UxSearchBundle\Event\PreSearchEvent;
use Mezcalito\UxSearchBundle\Search\AbstractSearch;
use Mezcalito\UxSearchBundle\Twig\Components\Facet\RangeInput;
use Mezcalito\UxSearchBundle\Twig\Components\Facet\RangeSlider;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;

#[AsSearch(Product::class, name: 'products', adapter: 'doctrine')]
class ProductSearch extends AbstractSearch
{
    public function __construct(
        private Security $security,
        private LoggerInterface $logger,
        private RouterInterface $router,
    ) {
    }

    public function build(array $options = []): void
    {
        $this
            // Adapter configuration
            ->setAdapterParameters([
                DoctrineAdapter::SEARCH_FIELDS => ['p.name', 'p.description', 'p.brand'],
                DoctrineAdapter::QUERY_BUILDER_ALIAS => 'p',
                DoctrineAdapter::QUERY_BUILDER => function (QueryBuilder $qb) {
                    $qb->andWhere('p.active = :active')
                       ->andWhere('p.stock > :min_stock')
                       ->setParameter('active', true)
                       ->setParameter('min_stock', 0);
                },
            ])

            // Facets
            ->addFacet('p.category', 'Category', null, ['limit' => 10])
            ->addFacet('p.brand', 'Brand', null, ['limit' => 20])
            ->addFacet('p.rating', 'Rating')
            ->addFacet('p.price', 'Price', RangeSlider::class)
            ->addFacet('p.weight', 'Weight (kg)', RangeInput::class)

            // Sorting options
            ->addAvailableSort(null, 'Relevance')
            ->addAvailableSort('p.price:asc', 'Price: Low to High')
            ->addAvailableSort('p.price:desc', 'Price: High to Low')
            ->addAvailableSort('p.rating:desc', 'Best Rated')
            ->addAvailableSort('p.createdAt:desc', 'Newest First')

            // Pagination
            ->setAvailableHitsPerPage([12, 24, 48, 96])

            // Pre-search event: Log queries
            ->addEventListener(PreSearchEvent::class, function (PreSearchEvent $event) {
                $this->logger->info('Product search', [
                    'query' => $event->getQuery()->getQuery(),
                    'user' => $this->security->getUser()?->getEmail(),
                ]);

                // Add filter for premium users
                if ($this->security->isGranted('ROLE_PREMIUM')) {
                    $event->getQuery()->addFilter('p.exclusive', ['true']);
                }
            }, priority: 10)

            // Post-search event: Enrich results
            ->addEventListener(PostSearchEvent::class, function (PostSearchEvent $event) {
                foreach ($event->getResultSet()->getHits() as $hit) {
                    $data = $hit->getData();

                    // Generate URL
                    $data['url'] = $this->router->generate('product_show', [
                        'id' => $data['id']
                    ]);

                    // Add discount badge
                    if ($data['discount'] > 0) {
                        $data['badge'] = sprintf('-%d%%', $data['discount']);
                    }

                    $hit->setData($data);
                }
            }, priority: 5)

            // URL rewriting
            ->enableUrlRewriting()
        ;
    }
}
```

---

## Tips and Best Practices

### 1. Facet Organization

Group related facets together and limit displayed values:

```php
// Primary filters (most important)
->addFacet('category', 'Category', null, ['limit' => 10])
->addFacet('brand', 'Brand', null, ['limit' => 15])

// Secondary filters
->addFacet('color', 'Color', null, ['limit' => 8])
->addFacet('size', 'Size', null, ['limit' => 6])

// Numeric filters last
->addFacet('price', 'Price', RangeSlider::class)
```

### 2. Event Priority Strategy

Use priority to control execution order:

```php
// 1. Data enrichment (high priority)
->addEventListener(PostSearchEvent::class, $enrichData, priority: 10)

// 2. Business logic (medium priority)
->addEventListener(PostSearchEvent::class, $applyDiscounts, priority: 5)

// 3. Analytics/logging (low priority)
->addEventListener(PostSearchEvent::class, $trackAnalytics, priority: 0)
```

### 3. Performance Optimization

```php
// Limit facet values to improve performance
->addFacet('brand', 'Brand', null, ['limit' => 20])

// Use appropriate hits per page options
->setAvailableHitsPerPage([12, 24, 48]) // Don't offer 100+

// In PostSearchEvent, only enrich necessary data
->addEventListener(PostSearchEvent::class, function (PostSearchEvent $event) {
    // Only fetch what's needed, not full entities
    $ids = array_map(fn($hit) => $hit->getData()['id'], $event->getResultSet()->getHits());
    $urls = $this->urlRepository->findUrlsByProductIds($ids); // Optimized query

    // Map URLs to hits
    foreach ($event->getResultSet()->getHits() as $hit) {
        $data = $hit->getData();
        $data['url'] = $urls[$data['id']] ?? '/';
        $hit->setData($data);
    }
})
```

### 4. Error Handling

```php
->addEventListener(PostSearchEvent::class, function (PostSearchEvent $event) {
    try {
        foreach ($event->getResultSet()->getHits() as $hit) {
            // Enrich data
        }
    } catch (\Exception $e) {
        // Log error but don't break the search
        $this->logger->error('Failed to enrich search results', [
            'error' => $e->getMessage()
        ]);
    }
})
```

---

## Further Reading

- [Algolia Adapter Configuration](algolia.md)
- [Meilisearch Adapter Configuration](meilisearch.md)
- [Doctrine Adapter Configuration](doctrine.md)
- [Component Customization](../components/)
