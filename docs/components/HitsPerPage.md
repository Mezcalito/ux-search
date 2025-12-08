# HitsPerPage Component

The `HitsPerPage` component displays a dropdown menu allowing users to control how many search results are displayed per page.

## Usage

```twig
<twig:Mezcalito:UxSearch:HitsPerPage />
```

## Available Variables

| Variable               | Type                | Description                          |
|------------------------|---------------------|--------------------------------------|
| `availableHitsPerPage` | array               | Array of available page size options |
| `activeHitPerPage`     | int                 | Currently selected page size         |
| `attributes`           | ComponentAttributes | HTML attributes for the container    |

## Blocks Available

| Block Name | Description                                                                                  |
|------------|----------------------------------------------------------------------------------------------|
| `content`  | Main block containing the dropdown - override to completely change the page size selector UI |

## Default Layout

```twig
{% block content %}
    <div {{ attributes.defaults({
        'class': 'ux-search-hits-per-page ux-search-select',
    }) }}>
        <select data-model="query.activeHitsPerPage">
            {% for option in availableHitsPerPage %}
                <option value="{{ option }}" {% if option == activeHitPerPage %}selected{% endif %}>{{ option }}</option>
            {% endfor %}
        </select>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down"><path d="m6 9 6 6 6-6"/></svg>
    </div>
{% endblock %}
```

## Default HTML Output

```html
<div class="ux-search-hits-per-page ux-search-select">
    <select data-model="query.activeHitsPerPage">
        <option value="3">3</option>
        <option value="6">6</option>
        <option value="12" selected="">12</option>
    </select>
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down"><path d="m6 9 6 6 6-6"></path></svg>
</div>
```

## Configuration

Define available page sizes in your Search class:

```php
use Mezcalito\UxSearchBundle\Search\AbstractSearch;

#[AsSearch('products')]
class ProductSearch extends AbstractSearch
{
    public function build(array $options = []): void
    {
        $this->setAvailableHitsPerPage([12, 24, 48, 96]);
    }
}
```

**Recommendations:**
- Offer 3-5 options to avoid overwhelming users
- Use incremental values (12, 24, 48) or doubling (10, 20, 40)
- Consider performance impact of large page sizes
- Smaller page sizes (12-24) are better for mobile

## Performance Considerations

**Impact of large page sizes:**
- Higher page sizes increase server load and response time
- More DOM elements can slow down browser rendering
- Consider lazy loading images for large page sizes

**Recommended limits:**
- E-commerce: 12-48 items
- Blog/Articles: 10-25 items
- Data tables: 25-100 rows
- Image galleries: 20-50 images

## Styling

Default classes:
- `.ux-search-hits-per-page` - Main container
- `.ux-search-select` - Shared select styling class

## Related Components

- [Pagination](Pagination.md) - Navigate through pages
- [TotalHits](TotalHits.md) - Show total result count
- [Hits](Hits.md) - Display the results
- [SortBy](SortBy.md) - Control sort order
- [Layout](Layout.md) - Root container (includes HitsPerPage in toolbar)
