# SearchInput

The `SearchInput` component is used to let users perform a text-based query.

## Block available

| name    | Description |
|---------|-------------|
| content | -           |


## Default layout

```twig
{% block content %}
    <input {{ attributes.defaults({
    'class': 'ux-search-search-input ux-search-input',
    'type': 'search',
    'data-model': 'debounce(400)|query.queryString',
    'placeholder': 'search.placeholder'|trans(domain='mezcalito_ux_search')
    }) }}>
{% endblock %}
```

## Default HTML output
```html
<input class="ux-search-search-input ux-search-input" type="search" data-model="debounce(400)|query.queryString" placeholder="Search here...">
```
