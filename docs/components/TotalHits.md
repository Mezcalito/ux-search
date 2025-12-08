# TotalHits Component

The `TotalHits` component displays the total number of matching search results, providing users with immediate feedback about the search query scope.

## Usage

```twig
<twig:Mezcalito:UxSearch:TotalHits />
```

## Available Variables

| Variable     | Type                | Description                       |
|--------------|---------------------|-----------------------------------|
| `totalHits`  | int                 | Total number of matching results  |
| `attributes` | ComponentAttributes | HTML attributes for the container |

## Blocks Available

| Block Name | Description                                                                     |
|------------|---------------------------------------------------------------------------------|
| `content`  | Main block containing the total hits display - override to customize formatting |

## Default Layout

```twig
{% block content %}
    <span {{ attributes.defaults({
        'class': 'ux-search-total-hits'
    }) }} >
        {{ 'results'|trans({'%count%': totalHits}, domain='mezcalito_ux_search') }}
    </span>
{% endblock %}
```

## Default HTML Output

```html
<div class="ux-search__stats">
    <span class="ux-search-total-hits">
        10000 results
    </span>
</div>
```

This ensures screen readers announce result count changes when users apply filters or search.

## Internationalization

The default translation key supports pluralization:

```yaml
# translations/mezcalito_ux_search.en.yaml
results: '{0} No results|{1} 1 result|]1,Inf[ %count% results'
```

```yaml
# translations/mezcalito_ux_search.fr.yaml
results: '{0} Aucun résultat|{1} 1 résultat|]1,Inf[ %count% résultats'
```

## Styling

Default classes:
- `.ux-search-total-hits` - Main span element containing the result count

## Related Components

- [Hits](Hits.md) - Display the actual search results
- [Pagination](Pagination.md) - Navigate through result pages
- [HitsPerPage](HitsPerPage.md) - Control results per page
- [Layout](Layout.md) - Root container (includes TotalHits in stats block)
