# CurrentRefinements Component

The `CurrentRefinements` component displays a list of all active filters and refinements applied to the current search, allowing users to see and remove individual filters.

## Usage

```twig
<twig:Mezcalito:UxSearch:CurrentRefinements />
```

## Available Variables

| Variable        | Type                | Description                                             |
|-----------------|---------------------|---------------------------------------------------------|
| `activeFilters` | array               | Array of active filter objects (term and range filters) |
| `attributes`    | ComponentAttributes | HTML attributes for the container                       |

## Blocks Available

| Block Name | Description                                                                           |
|------------|---------------------------------------------------------------------------------------|
| `content`  | Main block wrapping all active refinements - override to change the overall structure |

## Default Layout

```twig
{% block content %}
    <div {{ attributes.defaults({
    'class': 'ux-search-current-refinements'
    }) }}>
        <ul class="ux-search-current-refinements__list">
            {%- for active_filter in activeFilters %}
                {% if ux_search_is_term_filter(active_filter) %}
                    {% for value in active_filter.values %}
                        <li class="ux-search-current-refinements__item">
                            <span class="ux-search-current-refinements__value">{{ value }}</span>
                            <button
                                    class="ux-search-current-refinements__remove"
                                    type="button"
                                    data-action="live#action:prevent"
                                    data-live-action-param="toggleFacetTerm"
                                    data-live-property-param="{{ active_filter.property }}"
                                    data-live-value-param="{{ value }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                <span class="ux-search-sr-only">{{ 'remove'|trans(domain='mezcalito_ux_search') }}</span>
                            </button>
                        </li>
                    {% endfor %}
                {% elseif ux_search_is_range_filter(active_filter) %}
                    {% if active_filter.min is not null %}
                        <li class="ux-search-current-refinements__item">
                            <span class="ux-search-current-refinements__value">{{ active_filter.property }} >= {{ active_filter.min }}</span>

                            <button
                                    class="ux-search-current-refinements__remove"
                                    type="button"
                                    data-action="live#action:prevent"
                                    data-live-action-param="updateFacetRange"
                                    data-live-property-param="{{ active_filter.property }}"
                                    {% if active_filter.max is not null %}data-live-max-param="{{ active_filter.max }}"{% endif %}
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                <span class="ux-search-sr-only">{{ 'remove'|trans(domain='mezcalito_ux_search') }}</span>
                            </button>
                        </li>
                    {% endif %}

                    {% if active_filter.max is not null %}
                        <li class="ux-search-current-refinements__item">
                            <span class="ux-search-current-refinements__value">{{ active_filter.property }} <= {{ active_filter.max }}</span>

                            <button
                                    class="ux-search-current-refinements__remove"
                                    type="button"
                                    data-action="live#action:prevent"
                                    data-live-action-param="updateFacetRange"
                                    data-live-property-param="{{ active_filter.property }}"
                                    {% if active_filter.min is not null %}data-live-min-param="{{ active_filter.min }}"{% endif %}
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                <span class="ux-search-sr-only">{{ 'remove'|trans(domain='mezcalito_ux_search') }}</span>
                            </button>
                        </li>
                    {% endif %}
                {% endif %}
            {% endfor -%}
        </ul>
    </div>
{% endblock %}
```

## Default HTML Output

```html
<div class="ux-search-current-refinements">
    <ul class="ux-search-current-refinements__list">                                                            <li class="ux-search-current-refinements__item">
        <span class="ux-search-current-refinements__value">Apple</span>
        <button class="ux-search-current-refinements__remove" type="button" data-action="live#action:prevent" data-live-action-param="toggleFacetTerm" data-live-property-param="brand" data-live-value-param="Apple">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
            <span class="ux-search-sr-only">Remove</span>
        </button>
    </li>
        <li class="ux-search-current-refinements__item">
            <span class="ux-search-current-refinements__value">HP</span>
            <button class="ux-search-current-refinements__remove" type="button" data-action="live#action:prevent" data-live-action-param="toggleFacetTerm" data-live-property-param="brand" data-live-value-param="HP">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
                <span class="ux-search-sr-only">Remove</span>
            </button>
        </li>
        <li class="ux-search-current-refinements__item">
            <span class="ux-search-current-refinements__value">Ink cartridges</span>
            <button class="ux-search-current-refinements__remove" type="button" data-action="live#action:prevent" data-live-action-param="toggleFacetTerm" data-live-property-param="type" data-live-value-param="Ink cartridges">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
                <span class="ux-search-sr-only">Remove</span>
            </button>
        </li>
        <li class="ux-search-current-refinements__item">
            <span class="ux-search-current-refinements__value">2</span>
            <button class="ux-search-current-refinements__remove" type="button" data-action="live#action:prevent" data-live-action-param="toggleFacetTerm" data-live-property-param="rating" data-live-value-param="2">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
                <span class="ux-search-sr-only">Remove</span>
            </button>
        </li>
    </ul>
</div>
```

## Helper Functions

The component uses Twig functions to distinguish filter types:

- `ux_search_is_term_filter(filter)` - Returns true if filter is a term/facet filter
- `ux_search_is_range_filter(filter)` - Returns true if filter is a range filter

## Styling

Default classes:
- `.ux-search-current-refinements` - Main container
- `.ux-search-current-refinements__list` - List wrapper
- `.ux-search-current-refinements__item` - Individual filter item
- `.ux-search-current-refinements__value` - Filter value text
- `.ux-search-current-refinements__remove` - Remove button

## Related Components

- [ClearRefinements](ClearRefinements.md) - Clear all filters at once
- [Facet/RefinementList](Facet/RefinementList.md) - Select term filters
- [Facet/RangeSlider](Facet/RangeSlider.md) - Select range filters
- [Facet/RangeInput](Facet/RangeInput.md) - Input range filters
- [Layout](Layout.md) - Root container (includes CurrentRefinements in toolbar)
