# Meilisearch Adapter

Meilisearch is a powerful open-source search engine that you can self-host. It provides fast, relevant search results with a simple setup and excellent developer experience.

## Why Use Meilisearch?

**Best for:**
- Self-hosted search solution with full control
- Cost-effective alternative to Algolia for production
- Fast, typo-tolerant search out of the box
- Simple setup and maintenance
- Open-source with active community

**Consider alternatives if:**
- You need fully managed cloud service (use Algolia)
- You have very small datasets (use Doctrine)
- You need advanced features like personalization or A/B testing

## Installation

The Meilisearch adapter requires the official PHP client and a PSR-7/PSR-18 implementation (e.g. `nyholm/psr7`):

```bash
composer require meilisearch/meilisearch-php nyholm/psr7
```

## Configuration

### Configure the Adapter

```yaml
# config/packages/mezcalito_ux_search.yaml
mezcalito_ux_search:
    adapters:
        meilisearch: 'meilisearch://YOUR_API_KEY@localhost:7700'
        # Or for production with HTTPS:
        # meilisearch: 'meilisearch://YOUR_API_KEY@search.example.com:443'
```

Get your API key from Meilisearch dashboard or use the master key for development.

## Available Configuration Parameters

| Constant name                 | Meilisearch name      | Type     | Default value | Description                              |
|-------------------------------|-----------------------|----------|---------------|------------------------------------------|
| ATTRIBUTES_TO_RETRIEVE_PARAM  | attributesToRetrieve  | string[] | ['*']         | Attributes to return in search results   |
| ATTRIBUTES_TO_CROP_PARAM      | attributesToCrop      | string[] | []            | Attributes to crop (truncate) in results |
| CROP_LENGTH_PARAM             | cropLength            | int      | 10            | Number of words to keep when cropping    |
| CROP_MARKER_PARAM             | cropMarker            | string   | ...           | String to indicate cropped text          |
| ATTRIBUTES_TO_HIGHLIGHT_PARAM | attributesToHighlight | string[] | []            | Attributes to highlight matching terms   |
| HIGHLIGHT_PRE_TAG_PARAM       | highlightPreTag       | string   | &lt;em&gt;    | Opening tag for highlighted terms        |
| HIGHLIGHT_POST_TAG_PARAM      | highlightPostTag      | string   | &lt;/em&gt;   | Closing tag for highlighted terms        |
| DISTINCT_PARAM                | distinct              | string   | null          | Attribute to deduplicate results by      |

## Complete Example

Here's a complete Search class using the Meilisearch adapter with highlighting and cropping:

```php
<?php

namespace App\Search;

use Mezcalito\UxSearchBundle\Adapter\Meilisearch\MeilisearchAdapter;
use Mezcalito\UxSearchBundle\Attribute\AsSearch;
use Mezcalito\UxSearchBundle\Event\PostSearchEvent;
use Mezcalito\UxSearchBundle\Search\AbstractSearch;
use Mezcalito\UxSearchBundle\Twig\Components\Facet\RangeSlider;

#[AsSearch('products', name: 'products', adapter: 'meilisearch')]
class ProductSearch extends AbstractSearch
{
    public function build(array $options = []): void
    {
        $this
            ->setAdapterParameters([
                // Crop long descriptions
                MeilisearchAdapter::ATTRIBUTES_TO_CROP_PARAM => ['description'],
                MeilisearchAdapter::CROP_LENGTH_PARAM => 20,
                MeilisearchAdapter::CROP_MARKER_PARAM => '...',

                // Highlight search terms
                MeilisearchAdapter::ATTRIBUTES_TO_HIGHLIGHT_PARAM => ['name', 'description'],
                MeilisearchAdapter::HIGHLIGHT_PRE_TAG_PARAM => '<mark class="highlight">',
                MeilisearchAdapter::HIGHLIGHT_POST_TAG_PARAM => '</mark>',

                // Retrieve specific fields only
                MeilisearchAdapter::ATTRIBUTES_TO_RETRIEVE_PARAM => [
                    'id', 'name', 'description', 'price', 'image', 'brand'
                ],
            ])
            ->addFacet('type', 'Product Type', null, ['limit' => 10])
            ->addFacet('brand', 'Brand')
            ->addFacet('rating', 'Rating')
            ->addFacet('price_range', 'Price Range')
            ->addFacet('price', 'Price', RangeSlider::class)
            ->setAvailableHitsPerPage([12, 24, 48])
            ->addAvailableSort(null, 'Relevance')
            ->addAvailableSort('price:asc', 'Price ↑')
            ->addAvailableSort('price:desc', 'Price ↓')
            ->addAvailableSort('popularity:desc', 'Most Popular')
            ->enableUrlRewriting()
        ;
    }
}
```

## Common Use Cases

### Highlighting Search Terms

Highlight matching terms in search results:

```php
->setAdapterParameters([
    MeilisearchAdapter::ATTRIBUTES_TO_HIGHLIGHT_PARAM => ['name', 'description', 'brand'],
    MeilisearchAdapter::HIGHLIGHT_PRE_TAG_PARAM => '<strong class="text-yellow-400">',
    MeilisearchAdapter::HIGHLIGHT_POST_TAG_PARAM => '</strong>',
])
```

Meilisearch will add these tags around matching words. Style them in your CSS:

```css
.text-yellow-400 {
    background-color: #ffd700;
    font-weight: 600;
    padding: 0 2px;
}
```

### Cropping Long Text

Automatically truncate long descriptions to show relevant excerpts:

```php
->setAdapterParameters([
    MeilisearchAdapter::ATTRIBUTES_TO_CROP_PARAM => ['description', 'content'],
    MeilisearchAdapter::CROP_LENGTH_PARAM => 15,  // Keep 15 words
    MeilisearchAdapter::CROP_MARKER_PARAM => '…', // Use ellipsis
])
```

**Before cropping:**
> "This is a fantastic product with amazing features that will change your life and make everything better for everyone in the world..."

**After cropping (with matching term "fantastic"):**
> "This is a fantastic product with amazing features that will change your life and make…"

### Deduplication with Distinct

Remove duplicate results based on a specific attribute:

```php
->setAdapterParameters([
    MeilisearchAdapter::DISTINCT_PARAM => 'product_group_id',
])
```

Use cases:
- Show only one variant per product group
- Remove duplicate articles from different sources
- Display unique items when you have multiple records per entity

**Example:**

Without distinct, searching for "iPhone" might return:
- iPhone 13 - 128GB - Blue
- iPhone 13 - 256GB - Blue
- iPhone 13 - 128GB - Red
- iPhone 13 - 256GB - Red

With `distinct: 'product_group_id'`:
- iPhone 13 - 128GB - Blue *(others hidden)*

### Optimizing Payload Size

Retrieve only the fields you need for display:

```php
->setAdapterParameters([
    MeilisearchAdapter::ATTRIBUTES_TO_RETRIEVE_PARAM => [
        'id',
        'name',
        'price',
        'image_url',
        'rating',
    ],
])
```

Benefits:
- Faster response times
- Less bandwidth usage
- Smaller payload for frontend

### Combined: Highlight + Crop

Perfect for search result pages with previews:

```php
->setAdapterParameters([
    // Highlight matching terms
    MeilisearchAdapter::ATTRIBUTES_TO_HIGHLIGHT_PARAM => ['title', 'content'],
    MeilisearchAdapter::HIGHLIGHT_PRE_TAG_PARAM => '<mark>',
    MeilisearchAdapter::HIGHLIGHT_POST_TAG_PARAM => '</mark>',

    // Crop long content to show relevant excerpt
    MeilisearchAdapter::ATTRIBUTES_TO_CROP_PARAM => ['content'],
    MeilisearchAdapter::CROP_LENGTH_PARAM => 30,
    MeilisearchAdapter::CROP_MARKER_PARAM => '...',

    // Only retrieve what's needed
    MeilisearchAdapter::ATTRIBUTES_TO_RETRIEVE_PARAM => [
        'id', 'title', 'content', 'author', 'created_at'
    ],
])
```

## Integration with Events

Modify results after search:

```php
use Mezcalito\UxSearchBundle\Event\PostSearchEvent;

->addEventListener(PostSearchEvent::class, function (PostSearchEvent $event) {
    foreach ($event->getResultSet()->getHits() as $hit) {
        $data = $hit->getData();

        // Add computed fields
        $data['discount'] = $this->calculateDiscount($data['price']);
        $data['in_stock'] = $this->checkStock($data['id']);

        // Generate URLs
        $data['url'] = $this->router->generate('product_show', [
            'slug' => $data['slug']
        ]);

        $hit->setData($data);
    }
}, priority: 2)
```

## Performance Optimization

### Limit Retrieved Attributes

```php
->setAdapterParameters([
    MeilisearchAdapter::ATTRIBUTES_TO_RETRIEVE_PARAM => [
        'id', 'name', 'price', 'image' // Only what you display
    ],
])
```

### Use Cropping Wisely

```php
->setAdapterParameters([
    MeilisearchAdapter::ATTRIBUTES_TO_CROP_PARAM => ['description'],
    MeilisearchAdapter::CROP_LENGTH_PARAM => 15, // Keep it short
])
```

### Optimize Facets

Configure facets to only retrieve what's needed:

```php
->addFacet('brand', 'Brand', null, ['limit' => 20]) // Limit facet values
```

## Troubleshooting

### No results returned

**Check:**
- Meilisearch server is running: `curl http://localhost:7700/health`
- Index exists: `curl http://localhost:7700/indexes`
- Documents are indexed: `curl http://localhost:7700/indexes/products/documents`
- Index name in `#[AsSearch]` matches your Meilisearch index name

### Facets not working

**Solution:**
- Declare attributes as filterable in index settings
- Verify attributes exist in your indexed documents
- Check attribute names match between Search class and indexed data

### Sorting not working

**Solution:**
- Declare attributes as sortable in index settings
- Use format `attribute:direction` (e.g., `'price:asc'`)
- Verify attribute contains sortable values (numbers, timestamps)

### Highlighting not showing

**Check:**
- Attributes are declared in `ATTRIBUTES_TO_HIGHLIGHT_PARAM`
- Highlight tags are rendered in your Twig templates
- CSS doesn't override highlight styles

### Slow searches

**Optimize:**
- Reduce `ATTRIBUTES_TO_RETRIEVE_PARAM` to only needed fields
- Lower `CROP_LENGTH_PARAM` value
- Limit number of facets
- Add more RAM to Meilisearch server
- Use SSD for Meilisearch data directory

## Reference Implementation

See the complete working example in the test application:
[tests/TestApplication/src/Search/MeilisearchSearch.php](../../tests/TestApplication/src/Search/MeilisearchSearch.php)

## Further Reading

For more information about Meilisearch features and configuration, check the [Meilisearch documentation](https://www.meilisearch.com/docs/reference/api/search#body).
