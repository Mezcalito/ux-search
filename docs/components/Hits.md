# Hits

The `Hits` component displays a list of refinements applied to the search.

## Block available

| name     | Description |
|----------|-------------|
| content  | -           |
| hit      | -           |
| noResult | -           |

## Default layout

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

## Default HTML output
```html
<div class="ux-search-hits">
    <div class="ux-search-hits__list">
        <div class="ux-search-hits__item" style="max-width: 100%; overflow: auto;">
            <pre style="font-family: monospace">
                // ...
            </pre>
        </div>
        <div class="ux-search-hits__item" style="max-width: 100%; overflow: auto;">
            <pre style="font-family: monospace">
                // ...
            </pre>
        </div>
        // ...
    </div>
</div>
```
