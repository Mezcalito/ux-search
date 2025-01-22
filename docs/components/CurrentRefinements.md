# CurrentRefinements

The `CurrentRefinements` component displays a list of refinements applied to the search.

## Block available

| name    | Description |
|---------|-------------|
| content | -           |


## Default layout

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

## Default HTML output
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
