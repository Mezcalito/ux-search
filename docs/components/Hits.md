# Hits Component

The `Hits` component displays the list of search results. By default, it shows results as JSON for debugging, but should be customized to display your actual product/content data.

## Usage

```twig
<twig:Mezcalito:UxSearch:Hits />
```

## Available Variables

| Variable       | Type                | Description                              |
|----------------|---------------------|------------------------------------------|
| `results`      | ResultSet           | Contains hits, total results, and facets |
| `results.hits` | Hit[]               | Array of search result items             |
| `hit.data`     | array\|object       | Individual hit data                      |
| `hit.score`    | float               | Relevance score                          |
| `attributes`   | ComponentAttributes | HTML attributes for the container        |

## Blocks Available

| Block Name | Description                                                                 |
|------------|-----------------------------------------------------------------------------|
| `content`  | Main block wrapping all hits - override to change container structure       |
| `hit`      | Individual hit rendering - **customize this** to display your data properly |
| `noResult` | Message shown when no results found                                         |

## Default Layout

```twig
{% block content %}
    <div {{ attributes.defaults({
            class: 'ux-search-hits'
    }) }}>
        {% if results.hits|length > 0 %}
            <div class="ux-search-hits__list">
                {% for hit in results.hits %}
                    {% block hit %}
                        <div class="ux-search-hits__item" style="max-width: 100%; overflow: auto;">
                            <pre style="font-family: monospace">{{ hit.data|json_encode(constant('JSON_PRETTY_PRINT')) }}</pre>
                        </div>
                    {% endblock %}
                {% endfor %}
            </div>
        {% else %}
            {% block noResult %}
                <div class="ux-search-hits__no-result">{{ 'no_result'|trans(domain='mezcalito_ux_search') }}</div>
            {% endblock %}
        {% endif %}
    </div>
{% endblock %}
```

## Default HTML Output

```html
<div class="ux-search-hits">
    <div class="ux-search-hits__list">
        <div class="ux-search-hits__item" style="max-width: 100%; overflow: auto;">
            <pre style="font-family: monospace">
                // JSON data here...
            </pre>
        </div>
        <!-- More hits... -->
    </div>
</div>
```

## Customization

### Basic Product Display

Create `templates/components/Mezcalito/UxSearch/Hits.html.twig`:

```twig
{% extends '@MezcalitoUxSearch/Hits.html.twig' %}

{% block hit %}
    <article class="product-card">
        <img src="{{ hit.data.image }}" alt="{{ hit.data.name }}" class="product-image">
        <div class="product-info">
            <h3 class="product-title">{{ hit.data.name }}</h3>
            <p class="product-price">${{ hit.data.price }}</p>
            <a href="{{ hit.data.url }}" class="product-link">View Details</a>
        </div>
    </article>
{% endblock %}
```

### Grid Layout

```twig
{% extends '@MezcalitoUxSearch/Hits.html.twig' %}

{% block content %}
    <div {{ attributes.defaults({ class: 'ux-search-hits' }) }}>
        {% if results.hits|length > 0 %}
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                {% for hit in results.hits %}
                    {% block hit %}
                        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                            <img src="{{ hit.data.image }}" alt="{{ hit.data.name }}" class="w-full h-48 object-cover">
                            <div class="p-4">
                                <h3 class="font-semibold text-lg mb-2">{{ hit.data.name }}</h3>
                                <p class="text-gray-600 text-sm mb-3">{{ hit.data.brand }}</p>
                                <div class="flex justify-between items-center">
                                    <span class="text-xl font-bold">${{ hit.data.price }}</span>
                                    <a href="{{ hit.data.url }}" class="btn btn-primary">View</a>
                                </div>
                            </div>
                        </div>
                    {% endblock %}
                {% endfor %}
            </div>
        {% else %}
            {% block noResult %}
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No results found</h3>
                    <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filters</p>
                </div>
            {% endblock %}
        {% endif %}
    </div>
{% endblock %}
```

### With Highlighting

If using Algolia or Meilisearch with highlighting:

```twig
{% block hit %}
    <article class="product-card">
        {% if hit.data._highlightResult is defined %}
            <h3>{{ hit.data._highlightResult.name.value|raw }}</h3>
            <p>{{ hit.data._highlightResult.description.value|raw }}</p>
        {% else %}
            <h3>{{ hit.data.name }}</h3>
            <p>{{ hit.data.description }}</p>
        {% endif %}
        <span class="price">${{ hit.data.price }}</span>
    </article>
{% endblock %}
```

## Performance Tips

1. **Lazy load images**:
   ```twig
   <img src="{{ hit.data.image }}" loading="lazy" alt="{{ hit.data.name }}">
   ```

2. **Limit description length**:
   ```twig
   {{ hit.data.description|slice(0, 200) }}...
   ```

3. **Use PostSearchEvent to add URLs** (don't generate in template):
   ```php
   ->addEventListener(PostSearchEvent::class, function (PostSearchEvent $event) {
       foreach ($event->getResultSet()->getHits() as $hit) {
           $data = $hit->getData();
           $data['url'] = $this->router->generate('product', ['id' => $data['id']]);
           $hit->setData($data);
       }
   })
   ```

## Styling

Default classes:
- `.ux-search-hits` - Main container
- `.ux-search-hits__list` - Hits list wrapper
- `.ux-search-hits__item` - Individual hit item
- `.ux-search-hits__no-result` - No results message

## Related Components

- [Pagination](Pagination.md) - Navigate through pages
- [TotalHits](TotalHits.md) - Show total results count
- [HitsPerPage](HitsPerPage.md) - Control results per page
- [Layout](Layout.md) - Root container
