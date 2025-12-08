# SearchInput Component

The `SearchInput` component provides a text input field for users to enter search queries. It includes automatic debouncing to prevent excessive searches while typing.

## Usage

```twig
<twig:Mezcalito:UxSearch:SearchInput />
```

## Available Variables

| Variable     | Type                | Description                                           |
|--------------|---------------------|-------------------------------------------------------|
| `query`      | Query               | The current query object containing the search string |
| `attributes` | ComponentAttributes | HTML attributes for the input element                 |

## Blocks Available

| Block Name | Description                                                                                 |
|------------|---------------------------------------------------------------------------------------------|
| `content`  | Main block containing the input element - override to completely change the input structure |

## Default Layout

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

## Default HTML Output

```html
<input class="ux-search-search-input ux-search-input"
       type="search"
       data-model="debounce(400)|query.queryString"
       placeholder="Search here...">
```

## Styling

Default classes:
- `.ux-search-search-input` - Main input element
- `.ux-search-input` - Additional utility class

## Related Components

- [Layout](Layout.md) - Root container
- [CurrentRefinements](CurrentRefinements.md) - Show active searches
- [ClearRefinements](ClearRefinements.md) - Clear all filters
