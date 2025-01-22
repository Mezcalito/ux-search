# ClearRefinements

The `ClearRefinements` component displays a button that lets users clean every refinement applied to the search.

## Block available

| name    | Description |
|---------|-------------|
| content | -           |


## Default layout

```twig
{% block content %}
    {%- if activeFilters is defined and activeFilters|length > 0 %}
        <button 
            {{ attributes.defaults({
                'class': 'ux-search-clear-refinements ux-search-button',
                'data-action': 'live#action:prevent',
                'data-live-action-param': 'clearRefinements'
            }) }}
        >
            {{ 'reset_filters'|trans(domain='mezcalito_ux_search') }}
        </button>
    {% endif %}
{% endblock -%}
```

## Default HTML output
```html
<button class="ux-search-clear-refinements ux-search-button" data-action="live#action:prevent" data-live-action-param="clearRefinements">
    Reset filters
</button>
```
