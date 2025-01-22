# HitsPerPage

The `HitsPerPage` component displays a dropdown menu to let users change the number of displayed hits.

## Block available

| name     | Description |
|----------|-------------|
| content  | -           |

## Default layout

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

## Default HTML output
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
