# SortBy

The `SortBy` component displays a list of sorting possibility, allowing a user to change the way hits are sorted.

## Block available

| name    | Description |
|---------|-------------|
| content |             |


## Default layout

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

## Default HTML output
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
