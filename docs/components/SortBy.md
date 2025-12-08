# SortBy Component

The `SortBy` component displays a dropdown menu allowing users to change the sort order of search results (e.g., by price, popularity, relevance).

## Usage

```twig
<twig:Mezcalito:UxSearch:SortBy />
```

## Available Variables

| Variable         | Type                | Description                                             |
|------------------|---------------------|---------------------------------------------------------|
| `availableSorts` | array               | Array of sort options with `key` and `label` properties |
| `activeSort`     | string              | Currently selected sort key                             |
| `attributes`     | ComponentAttributes | HTML attributes for the container                       |

## Blocks Available

| Block Name | Description |
|-----------|-------------|
| `content` | Main block containing the sort dropdown - override to completely change the sort UI |

## Default Layout

```twig
{% block content %}
    <div {{ attributes.defaults({
        'class': 'ux-search-sort-by ux-search-select',
    }) }}>
        <select data-model="query.activeSort">
            {% for option in availableSorts %}
                <option value="{{ option.key }}" {% if option.key == activeSort %}selected{% endif %}>{{ option.label }}</option>
            {% endfor %}
        </select>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down"><path d="m6 9 6 6 6-6"/></svg>
    </div>
{% endblock %}
```

## Default HTML Output

```html
<div class="ux-search-sort-by ux-search-select">
    <select data-model="query.activeSort">
        <option value="price:asc" selected>Price ↑</option>
        <option value="price:desc">Price ↓</option>
        <option value="popularity:asc">Popularity ↑</option>
        <option value="popularity:desc">Popularity ↓</option>
    </select>
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down"><path d="m6 9 6 6 6-6"></path></svg>
</div>
```

## Configuration

Define sort options in your Search class:

```php
use Mezcalito\UxSearchBundle\Search\AbstractSearch;

#[AsSearch('products')]
class ProductSearch extends AbstractSearch
{
    public function build(array $options = []): void
    {
        $this
            ->addAvailableSort('relevance', 'Relevance')
            ->addAvailableSort('price:asc', 'Price: Low to High')
            ->addAvailableSort('price:desc', 'Price: High to Low')
            ->addAvailableSort('name:asc', 'Name: A to Z')
            ->addAvailableSort('created:desc', 'Newest First')
            ->addAvailableSort('rating:desc', 'Top Rated')
        ;
    }
}
```

**Note:** Sort key format depends on your adapter:
- **Meilisearch/Doctrine**: `field:direction` (e.g., `price:asc`)
- **Algolia**: Uses replica index names (e.g., `products_price_asc`)

See [Customize Your Search](../usage/customize-your-search.md#sorting) for details.

## Styling

Default classes:
- `.ux-search-sort-by` - Main container
- `.ux-search-select` - Shared select styling class

## Related Components

- [HitsPerPage](HitsPerPage.md) - Control results per page
- [Hits](Hits.md) - Display sorted results
- [CurrentRefinements](CurrentRefinements.md) - Show active filters
- [Layout](Layout.md) - Root container (includes SortBy in toolbar)
