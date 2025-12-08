# Algolia Adapter

Algolia is a powerful-hosted search engine that provides fast, relevant search results with advanced features like typo tolerance, faceting, and analytics.

## Why Use Algolia?

**Best for:**
- Production applications requiring best-in-class search performance
- Large catalogs with complex search requirements
- Advanced features (typo tolerance, synonyms, personalization, A/B testing)
- Global, distributed search infrastructure
- Detailed analytics and insights

**Consider alternatives if:**
- You need self-hosted solution (use Meilisearch)
- Budget constraints (Algolia is a paid service)
- Small datasets where Doctrine is sufficient

## Configuration

To use Algolia, configure your adapter DSN:

```yaml
# config/packages/mezcalito_ux_search.yaml
mezcalito_ux_search:
    adapters:
        algolia: 'algolia://YOUR_API_KEY@YOUR_APP_ID'
```

Get your API credentials from your [Algolia dashboard](https://dashboard.algolia.com/).

## Available Configuration Parameters

| Constant name                              | Algolia name                     | Type     | Default value                          | Description                                            |
|--------------------------------------------|----------------------------------|----------|----------------------------------------|--------------------------------------------------------|
| ADVANCED_SYNTAX_PARAM                      | advancedSyntax                   | bool     | false                                  | Enable advanced search syntax (AND, OR, NOT operators) |
| ADVANCED_SYNTAX_FEATURES_PARAM             | advancedSyntaxFeatures           | string[] | ['exactPhrase', 'excludeWords']        | Control which advanced syntax features are enabled     |
| ALLOW_TYPOS_ON_NUMERIC_TOKENS_PARAM        | allowTyposOnNumericTokens        | bool     | true                                   | Allow typo tolerance on numbers                        |
| ALTERNATIVES_AS_EXACT_PARAM                | alternativesAsExact              | string[] | ['ignorePlurals', 'singleWordSynonym'] | Treat certain alternatives as exact matches            |
| ANALYTICS_PARAM                            | analytics                        | bool     | true                                   | Enable search analytics                                |
| ANALYTICS_TAGS_PARAM                       | analyticsTags                    | string[] | []                                     | Tags for analytics segmentation                        |
| ATTRIBUTES_TO_HIGHLIGHT_PARAM              | attributesToHighlight            | string[] | ['*']                                  | Attributes to highlight in results                     |
| ATTRIBUTES_TO_RETRIEVE_PARAM               | attributesToRetrieve             | string[] | ['*']                                  | Attributes to return in results                        |
| DECOMPOUND_QUERY_PARAM                     | decompoundQuery                  | bool     | true                                   | Split compound words (German, Dutch, etc.)             |
| DISABLE_EXACT_ON_ATTRIBUTES_PARAM          | disableExactOnAttributes         | string[] | []                                     | Disable exact match for specified attributes           |
| DISABLE_TYPO_TOLERANCE_ON_ATTRIBUTES_PARAM | disableTypoToleranceOnAttributes | string[] | []                                     | Disable typo tolerance on specified attributes         |
| ENABLE_AB_TEST_PARAM                       | enableABTest                     | bool     | true                                   | Enable A/B testing                                     |
| ENABLE_PERSONALIZATION_PARAM               | enablePersonalization            | bool     | false                                  | Enable personalized results                            |
| FACETING_AFTER_DISTINCT                    | facetingAfterDistinct            | bool     | false                                  | Calculate facets after deduplication                   |
| HIGHLIGHT_POST_TAG_PARAM                   | highlightPostTag                 | string   | &lt;/em&gt;                            | Closing tag for highlighting                           |
| HIGHLIGHT_PRE_TAG_PARAM                    | highlightPreTag                  | string   | &lt;em&gt;                             | Opening tag for highlighting                           |
| MAX_VALUES_PER_FACET_PARAM                 | maxValuesPerFacet                | int      | 100                                    | Maximum facet values to return                         |
| QUERY_TYPE_PARAM                           | queryType                        | string   | prefixLast                             | How to match query words                               |
| SORT_FACET_VALUES_BY_PARAM                 | sortFacetValuesBy                | string   | count                                  | Sort facet values by count or alpha                    |
| SYNONYMS_PARAM                             | synonyms                         | bool     | true                                   | Enable synonym matching                                |

### ALTERNATIVES_AS_EXACT_PARAM Values

This parameter accepts the following values:
- `'ignorePlurals'` - Treat plural/singular as exact matches
- `'singleWordSynonym'` - Treat single-word synonyms as exact matches
- `'multiWordsSynonym'` - Treat multi-word synonyms as exact matches
- `'ignorePlurals'` - Same as above (can be used multiple times)

## Complete Example

Here's a complete Search class using the Algolia adapter with customized highlighting:

```php
<?php

namespace App\Search;

use Mezcalito\UxSearchBundle\Adapter\Algolia\AlgoliaAdapter;
use Mezcalito\UxSearchBundle\Attribute\AsSearch;
use Mezcalito\UxSearchBundle\Event\PostSearchEvent;
use Mezcalito\UxSearchBundle\Search\AbstractSearch;
use Mezcalito\UxSearchBundle\Twig\Components\Facet\RangeSlider;

#[AsSearch('products_index', name: 'products', adapter: 'algolia')]
class ProductSearch extends AbstractSearch
{
    public function build(array $options = []): void
    {
        $this
            ->setAdapterParameters([
                // Customize highlighting tags
                AlgoliaAdapter::ATTRIBUTES_TO_HIGHLIGHT_PARAM => ['name', 'description'],
                AlgoliaAdapter::HIGHLIGHT_PRE_TAG_PARAM => '<mark class="highlight">',
                AlgoliaAdapter::HIGHLIGHT_POST_TAG_PARAM => '</mark>',

                // Limit facet values
                AlgoliaAdapter::MAX_VALUES_PER_FACET_PARAM => 50,

                // Enable analytics with tags
                AlgoliaAdapter::ANALYTICS_PARAM => true,
                AlgoliaAdapter::ANALYTICS_TAGS_PARAM => ['web', 'products'],
            ])
            ->addFacet('type', 'Product Type', null, ['limit' => 10])
            ->addFacet('brand', 'Brand')
            ->addFacet('rating', 'Rating')
            ->addFacet('price', 'Price', RangeSlider::class)
            ->addAvailableSort('products_index', 'Relevance')
            ->addAvailableSort('products_index_price_asc', 'Price ↑')
            ->addAvailableSort('products_index_price_desc', 'Price ↓')
            ->setAvailableHitsPerPage([12, 24, 48])
            ->enableUrlRewriting()
        ;
    }
}
```

## Common Use Cases

### Custom Highlighting

Highlight search terms with custom HTML tags:

```php
->setAdapterParameters([
    AlgoliaAdapter::ATTRIBUTES_TO_HIGHLIGHT_PARAM => ['name', 'description', 'brand'],
    AlgoliaAdapter::HIGHLIGHT_PRE_TAG_PARAM => '<strong class="search-highlight">',
    AlgoliaAdapter::HIGHLIGHT_POST_TAG_PARAM => '</strong>',
])
```

Then style the highlights in your CSS:

```css
.search-highlight {
    background-color: yellow;
    font-weight: bold;
}
```

### Analytics and Tracking

Track search queries by user segment:

```php
->setAdapterParameters([
    AlgoliaAdapter::ANALYTICS_PARAM => true,
    AlgoliaAdapter::ANALYTICS_TAGS_PARAM => ['premium-users', 'mobile-app'],
])
```

View analytics in your Algolia dashboard to understand:
- Most popular searches
- Searches with no results
- Click-through rates
- Conversion tracking

### Advanced Search Syntax

Enable power users to use advanced operators:

```php
->setAdapterParameters([
    AlgoliaAdapter::ADVANCED_SYNTAX_PARAM => true,
    AlgoliaAdapter::ADVANCED_SYNTAX_FEATURES_PARAM => [
        'exactPhrase',   // "exact phrase" with quotes
        'excludeWords',  // -excluded words with minus
    ],
])
```

Users can then search:
- `"laptop computer"` - Exact phrase
- `laptop -apple` - Laptop but not Apple

### Personalization

Enable personalized search results based on user preferences:

```php
->setAdapterParameters([
    AlgoliaAdapter::ENABLE_PERSONALIZATION_PARAM => true,
])
```

Requires setting up personalization strategy in Algolia dashboard.

### A/B Testing

Test different search configurations:

```php
->setAdapterParameters([
    AlgoliaAdapter::ENABLE_AB_TEST_PARAM => true,
])
```

Create A/B tests in Algolia dashboard to compare:
- Different ranking formulas
- Synonym configurations
- Facet orderings

### Typo Tolerance Control

Fine-tune typo tolerance per attribute:

```php
->setAdapterParameters([
    AlgoliaAdapter::DISABLE_TYPO_TOLERANCE_ON_ATTRIBUTES_PARAM => ['sku', 'reference'],
    AlgoliaAdapter::ALLOW_TYPOS_ON_NUMERIC_TOKENS_PARAM => false,
])
```

Useful for:
- SKU/product codes (no typos wanted)
- Reference numbers
- Exact technical specifications

### Retrieving Specific Attributes

Optimize payload size by retrieving only needed attributes:

```php
->setAdapterParameters([
    AlgoliaAdapter::ATTRIBUTES_TO_RETRIEVE_PARAM => [
        'objectID',
        'name',
        'price',
        'image',
        'url',
    ],
])
```

Reduces bandwidth and improves performance.

## Integration with Events

Use PostSearchEvent to modify Algolia results:

```php
use Mezcalito\UxSearchBundle\Event\PostSearchEvent;

->addEventListener(PostSearchEvent::class, function (PostSearchEvent $event) {
    foreach ($event->getResultSet()->getHits() as $hit) {
        $data = $hit->getData();

        // Add computed fields
        $data['discount_percentage'] = $this->calculateDiscount($data);

        // Add URLs
        $data['url'] = $this->router->generate('product_show', [
            'id' => $data['objectID']
        ]);

        $hit->setData($data);
    }
}, priority: 10)
```

## Index Configuration

Your Algolia index should be configured with:

## Performance Optimization

### Reduce Payload

```php
->setAdapterParameters([
    AlgoliaAdapter::ATTRIBUTES_TO_RETRIEVE_PARAM => ['id', 'name', 'price', 'image'],
    AlgoliaAdapter::ATTRIBUTES_TO_HIGHLIGHT_PARAM => ['name'], // Only highlight name
])
```

### Optimize Facets

```php
->setAdapterParameters([
    AlgoliaAdapter::MAX_VALUES_PER_FACET_PARAM => 20, // Limit facet values
    AlgoliaAdapter::SORT_FACET_VALUES_BY_PARAM => 'count', // Most popular first
])
```

## Troubleshooting

### No results or slow searches

- Check your Algolia dashboard for index status
- Verify API credentials
- Ensure index name matches in `#[AsSearch]` attribute
- Check index configuration (searchable attributes, ranking)

### Highlighting not working

- Verify `ATTRIBUTES_TO_HIGHLIGHT_PARAM` includes the fields
- Check that fields are configured as searchable in index
- Ensure highlight tags are properly rendered in Twig template

### Facets not appearing

- Verify attributes are declared in `attributesForFaceting` in index settings
- Check that data actually contains values for those attributes
- Increase `MAX_VALUES_PER_FACET_PARAM` if needed

## Reference Implementation

See the complete working example in the test application:
[tests/TestApplication/src/Search/AlgoliaSearch.php](../../tests/TestApplication/src/Search/AlgoliaSearch.php)

## Further Reading

For more information about Algolia features and configuration, check the [Algolia documentation](https://www.algolia.com/doc/).
