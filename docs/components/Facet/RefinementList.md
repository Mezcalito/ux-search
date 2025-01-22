# RefinementList

The `RefinementList` component allow users can filter the dataset based on facets.

The widget only displays the most relevant facet values for the current search context.
The sort option only affects the facets that are returned by the engine, not which facets are returned.

| name      | Description |
|-----------|-------------|
| label     | -           |
| list      | -           |
| show_more | -           |

## Default layout

```twig
<fieldset {{ attributes.defaults({ 
    class: 'ux-search-facet ux-search-refinement-list',
    'data-skip-morph': true,
    'data-controller': 'ux-search--refinement-list',
    'data-ux-search--refinement-list-limit-value': this.limit,
    'data-ux-search--refinement-list-show-more-label-value': 'show_more'|trans(domain='mezcalito_ux_search'),
    'data-ux-search--refinement-list-show-less-label-value': 'show_less'|trans(domain='mezcalito_ux_search')
}) }}>
    <legend class="ux-search-facet__title ux-search-refinement-list__title">{% block label %}{{ label }}{% endblock %}</legend>
    
    {% block list %}
        <ul class="ux-search-refinement-list__list">
            {%- for key,value in distribution.values %}
                <li
                    class="ux-search-refinement-list__item{{ loop.index > this.limit ? ' ux-search-refinement-list__item--exceed-limit' }}"
                >
                    <input
                        class="ux-search-refinement-list__input"
                        value="{{ key }}"
                        type="checkbox"
                        id="{{ property }}-{{ key }}"
                        {%- if distribution.isChecked(key) %} checked {% endif -%}
                        data-action="live#action"
                        data-live-action-param="toggleFacetTerm"
                        data-live-property-param="{{ property }}"
                        data-live-value-param="{{ key }}"
                    >
                    <label class="ux-search-refinement-list__label" for="{{ property }}-{{ key }}">
                        <span class="ux-search-refinement-list__label-text">{{ key }}</span>
                        <span class="ux-search-refinement-list__count">{{ value }}</span>
                    </label>
                </li>
            {% endfor -%}
        </ul>
    {% endblock %}  

    {% if distribution.values|length > this.limit %}
        {% block show_more %}
            <button
                class="ux-search-refinement-list__show-more"
                type="button"
                data-ux-search--refinement-list-target="toggle"
                data-action="ux-search--refinement-list#toggleShowMore"
            >
                {{ 'show_more'|trans(domain='mezcalito_ux_search') }}
            </button>
        {% endblock %}
    {% endif %}
</fieldset>

```

## Default HTML output
```html
<fieldset class="ux-search-facet ux-search-refinement-list" data-skip-morph="" data-controller="ux-search--refinement-list" data-ux-search--refinement-list-limit-value="2" data-ux-search--refinement-list-show-more-label-value="Show more" data-ux-search--refinement-list-show-less-label-value="Show less">
    <legend class="ux-search-facet__title ux-search-refinement-list__title">Type</legend>
    <ul class="ux-search-refinement-list__list">                
        <li class="ux-search-refinement-list__item">
            <input class="ux-search-refinement-list__input" value="Trend cases" type="checkbox" id="type-Trend cases" data-action="live#action" data-live-action-param="toggleFacetTerm" data-live-property-param="type" data-live-value-param="Trend cases">
            <label class="ux-search-refinement-list__label" for="type-Trend cases">
                <span class="ux-search-refinement-list__label-text">Trend cases</span>
                <span class="ux-search-refinement-list__count">537</span>
            </label>
        </li>
        <li class="ux-search-refinement-list__item">
            <input class="ux-search-refinement-list__input" value="Ult protection cases" type="checkbox" id="type-Ult protection cases" data-action="live#action" data-live-action-param="toggleFacetTerm" data-live-property-param="type" data-live-value-param="Ult protection cases">
            <label class="ux-search-refinement-list__label" for="type-Ult protection cases">
                <span class="ux-search-refinement-list__label-text">Ult protection cases</span>
                <span class="ux-search-refinement-list__count">289</span>
            </label>
        </li>
        // ...
    </ul>
    <button class="ux-search-refinement-list__show-more" type="button" data-ux-search--refinement-list-target="toggle" data-action="ux-search--refinement-list#toggleShowMore">Show more</button>
</fieldset>
```
