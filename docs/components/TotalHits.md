# TotalHits

The `TotalHits` component displays the total number of matching hits.

## Block available

| name    | Description |
|---------|-------------|
| content | -           |


## Default layout

```twig
{% block content %}
    <span {{ attributes.defaults({
        'class': 'ux-search-total-hits'
    }) }} >
        {{ 'results'|trans({'%count%': totalHits}, domain='mezcalito_ux_search') }}
    </span>
{% endblock %}
```

## Default HTML output
```html
<div class="ux-search__stats">
    <span class="ux-search-total-hits">
        10000 results
    </span>
</div>
```
