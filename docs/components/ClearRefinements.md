# ClearRefinements Component

The `ClearRefinements` component displays a button that allows users to clear all applied filters and refinements at once, resetting the search to its initial state.

## Usage

```twig
<twig:Mezcalito:UxSearch:ClearRefinements />
```

## Available Variables

| Variable        | Type                | Description                                                  |
|-----------------|---------------------|--------------------------------------------------------------|
| `activeFilters` | array               | Array of currently active filters (used to show/hide button) |
| `attributes`    | ComponentAttributes | HTML attributes for the container                            |

## Blocks Available

| Block Name | Description                                                                                       |
|------------|---------------------------------------------------------------------------------------------------|
| `content`  | Main block containing the button - override to customize the clear button appearance and behavior |

## Default Layout

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

## Default HTML Output

```html
<button class="ux-search-clear-refinements ux-search-button" data-action="live#action:prevent" data-live-action-param="clearRefinements">
    Reset filters
</button>
```

## Behavior

The button:
- **Only appears** when there are active filters (`activeFilters|length > 0`)
- **Clears all refinements** including:
  - Term/facet filters (from RefinementList)
  - Range filters (from RangeSlider and RangeInput)
  - Search query string
- **Resets to initial state** of the search

## Internationalization

The default translation key:

```yaml
# translations/mezcalito_ux_search.en.yaml
reset_filters: 'Reset filters'
```

```yaml
# translations/mezcalito_ux_search.fr.yaml
reset_filters: 'Réinitialiser les filtres'
```

## Styling

Default classes:
- `.ux-search-clear-refinements` - Main button element
- `.ux-search-button` - Shared button styling class


## Related Components

- [CurrentRefinements](CurrentRefinements.md) - Display and remove individual filters
- [Facet/RefinementList](Facet/RefinementList.md) - Term filter selection
- [Facet/RangeSlider](Facet/RangeSlider.md) - Range filter selection
- [Facet/RangeInput](Facet/RangeInput.md) - Range filter input
- [SearchInput](SearchInput.md) - Search query input
- [Layout](Layout.md) - Root container (includes ClearRefinements in toolbar)
