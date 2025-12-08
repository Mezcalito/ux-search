# RefinementList Component

The `RefinementList` component allows users to filter search results based on facet values (terms). It displays a list of checkbox options with result counts, and supports a "show more" feature for facets with many values.

## Usage

```twig
<twig:Mezcalito:UxSearch:Facet:RefinementList property="brand" />
```

The property is automatically determined when using the generic Facet component:
```twig
{% for facet in search.facets %}
    <twig:Mezcalito:UxSearch:Facet :property="facet.property" />
{% endfor %}
```

## Available Variables

| Variable                        | Type                  | Description                                         |
|---------------------------------|-----------------------|-----------------------------------------------------|
| `property`                      | string                | The facet property name (e.g., "brand", "category") |
| `label`                         | string                | The display label for the facet                     |
| `distribution`                  | FacetTermDistribution | Object containing facet values and counts           |
| `distribution.values`           | array                 | Array of facet values with their counts             |
| `distribution.isChecked(value)` | bool                  | Whether a value is currently selected               |
| `limit`                         | int                   | Number of items to display before "show more"       |
| `attributes`                    | ComponentAttributes   | HTML attributes for the container                   |

## Blocks Available

| Block Name  | Description                                                              |
|-------------|--------------------------------------------------------------------------|
| `label`     | Facet title/legend - override to customize the facet heading             |
| `list`      | List of facet options - override to change checkbox structure or styling |
| `show_more` | "Show more" button - override to customize expand/collapse behavior      |

## Default Layout

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

## Default HTML Output

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

## Configuration

Configure facet behavior in your Search class:

```php
use Mezcalito\UxSearchBundle\Search\AbstractSearch;

#[AsSearch('products')]
class ProductSearch extends AbstractSearch
{
    public function build(array $options = []): void
    {
        $this
            ->addFacet('brand', 'Brand')
            ->addFacet('category', 'Category')
            ->addFacet('color', 'Color', null, ['limit' => 10]) // Show max 10 values
            ->addFacet('size', 'Size', null, ['limit' => 5]);
    }
}
```

**Options:**
- `limit` - Number of facet values to display before "show more" (default: defined in component)

## Styling

Default classes:
- `.ux-search-facet` - Shared facet container class
- `.ux-search-refinement-list` - Main fieldset element
- `.ux-search-refinement-list__title` - Facet title/legend
- `.ux-search-refinement-list__list` - List container
- `.ux-search-refinement-list__item` - Individual facet option
- `.ux-search-refinement-list__item--exceed-limit` - Hidden items beyond limit
- `.ux-search-refinement-list__input` - Checkbox input
- `.ux-search-refinement-list__label` - Label wrapper
- `.ux-search-refinement-list__label-text` - Value text
- `.ux-search-refinement-list__count` - Result count
- `.ux-search-refinement-list__show-more` - Show more/less button

## Related Components

- [RangeSlider](RangeSlider.md) - For numeric range filtering
- [RangeInput](RangeInput.md) - For manual range input
- [CurrentRefinements](../CurrentRefinements.md) - Display active filters
- [ClearRefinements](../ClearRefinements.md) - Clear all filters
- [Layout](../Layout.md) - Root container
