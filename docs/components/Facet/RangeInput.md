# RangeInput Component

The `RangeInput` component allows users to filter numeric ranges by entering minimum and maximum values in text input fields. This provides precise control compared to a slider.

## Usage

```twig
<twig:Mezcalito:UxSearch:Facet:RangeInput property="price" />
```

The property is automatically determined when using the generic Facet component:
```twig
{% for facet in search.facets %}
    <twig:Mezcalito:UxSearch:Facet :property="facet.property" />
{% endfor %}
```

## Available Variables

| Variable            | Type                | Description                                         |
|---------------------|---------------------|-----------------------------------------------------|
| `property`          | string              | The facet property name (e.g., "price", "rating")   |
| `label`             | string              | The display label for the facet                     |
| `facetStat`         | FacetStat           | Object containing min, max, and user-selected range |
| `facetStat.min`     | float               | Minimum value in dataset                            |
| `facetStat.max`     | float               | Maximum value in dataset                            |
| `facetStat.userMin` | float\|null         | User-selected minimum value                         |
| `facetStat.userMax` | float\|null         | User-selected maximum value                         |
| `attributes`        | ComponentAttributes | HTML attributes for the container                   |

## Blocks Available

| Block Name | Description                                                                |
|------------|----------------------------------------------------------------------------|
| `label`    | Facet title/legend - override to customize the facet heading               |
| `form`     | Form containing inputs and submit button - override to change input layout |
| `submit`   | Submit button - override to customize button appearance                    |

## Default Layout

```twig
<fieldset {{ attributes.defaults({
    'class': 'ux-search-facet ux-search-range-input',
    'data-skip-morph': true
}) }}>
    <legend class="ux-search-facet__title">{% block label %}{{ label }}{% endblock %}</legend>
    {% block form %}
        <form
            class="ux-search-range-input__form"
            data-action="submit->ux-search#updateFacetRange:prevent"
            data-ux-search-property-param="{{ property }}"
            data-ux-search-range-min-param="{{ facetStat.min }}"
            data-ux-search-range-max-param="{{ facetStat.max }}"
        >
            <div class="ux-search-range-input__box">
                <label class="ux-search-range-input__label ux-search-sr-only" for="{{ property }}-min">{{ 'range.min'|trans(domain='mezcalito_ux_search') }}</label>
                <input
                    class="ux-search-range-input__input ux-search-input"
                    type="number"
                    min="{{ facetStat.min }}"
                    max="{{ facetStat.max }}"
                    id="{{ property }}-min"
                    name="{{ property }}-min"
                    placeholder="{{ facetStat.min }}"
                    value="{{ facetStat.userMin }}"
                    step="any"
                >
            </div>
            <div class="ux-search-range-input__box">
                <label class="ux-search-range-input__label ux-search-sr-only" for="{{ property }}-max">{{ 'range.max'|trans(domain='mezcalito_ux_search') }}</label>
                <input
                    class="ux-search-range-input__input ux-search-input"
                    type="number"
                    min="{{ facetStat.min }}"
                    max="{{ facetStat.max }}"
                    id="{{ property }}-max"
                    name="{{ property }}-max"
                    placeholder="{{ facetStat.max }}"
                    value="{{ facetStat.userMax }}"
                    step="any"
                >
            </div>
            {% block submit %}
                <button class="ux-search-range-input__submit ux-search-button" type="submit">
                    {{ 'go'|trans(domain='mezcalito_ux_search') }}
                </button>
            {% endblock %}
        </form>
    {% endblock %}
</fieldset>
```

## Default HTML Output

```html
<fieldset class="ux-search-facet ux-search-range-input" data-skip-morph="">
    <legend class="ux-search-facet__title">Price</legend>
    <form class="ux-search-range-input__form" data-action="submit->ux-search#updateFacetRange:prevent" data-ux-search-property-param="o.price" data-ux-search-range-min-param="1.99" data-ux-search-range-max-param="4999.98">
        <div class="ux-search-range-input__box">
            <label class="ux-search-range-input__label ux-search-sr-only" for="o.price-min">Min</label>
            <input class="ux-search-range-input__input ux-search-input" type="number" min="1.99" max="4999.98" id="o.price-min" name="o.price-min" placeholder="1.99" value="" step="any">
        </div>
        <div class="ux-search-range-input__box">
            <label class="ux-search-range-input__label ux-search-sr-only" for="o.price-max">Max</label>
            <input class="ux-search-range-input__input ux-search-input" type="number" min="1.99" max="4999.98" id="o.price-max" name="o.price-max" placeholder="4999.98" value="" step="any">
        </div>
        <button class="ux-search-range-input__submit ux-search-button" type="submit">
            Go
        </button>
    </form>
</fieldset>
```

## Configuration

Configure range input in your Search class:

```php
use Mezcalito\UxSearchBundle\Search\AbstractSearch;
use Mezcalito\UxSearchBundle\Twig\Component\Facet\RangeInputComponent;

#[AsSearch('products')]
class ProductSearch extends AbstractSearch
{
    public function build(array $options = []): void
    {
        $this
            ->addFacet('price', 'Price Range', RangeInputComponent::class)
            ->addFacet('rating', 'Rating Range', RangeInputComponent::class)
            ->addFacet('year', 'Year', RangeInputComponent::class);
    }
}
```

## Behavior

The component:
- Submits range filter when "Go" button is clicked
- Validates min is not greater than max
- Uses dataset min/max as bounds
- Supports decimal values with `step="any"`
- Can be configured to auto-submit on input change

## Internationalization

Translation keys used:

```yaml
# translations/mezcalito_ux_search.en.yaml
range:
    min: 'Min'
    max: 'Max'
go: 'Go'
```

```yaml
# translations/mezcalito_ux_search.fr.yaml
range:
    min: 'Min'
    max: 'Max'
go: 'Valider'
```

## Styling

Default classes:
- `.ux-search-facet` - Shared facet container class
- `.ux-search-range-input` - Main fieldset element
- `.ux-search-facet__title` - Facet title/legend
- `.ux-search-range-input__form` - Form wrapper
- `.ux-search-range-input__box` - Input container
- `.ux-search-range-input__label` - Input label (screen reader only by default)
- `.ux-search-range-input__input` - Number input field
- `.ux-search-range-input__submit` - Submit button
- `.ux-search-input` - Shared input styling class
- `.ux-search-button` - Shared button styling class

## When to Use

**Use RangeInput when:**
- Users need precise control over numeric values
- The range is large (e.g., 0-10000)
- Decimal precision is important
- Users might type specific values

**Use RangeSlider when:**
- Visual exploration is preferred
- The range is moderate (e.g., 0-100)
- Approximate values are acceptable
- Touch interface is primary

## Related Components

- [RangeSlider](RangeSlider.md) - Alternative visual range selection with sliders
- [RefinementList](RefinementList.md) - For term/category filtering
- [CurrentRefinements](../CurrentRefinements.md) - Display active filters
- [ClearRefinements](../ClearRefinements.md) - Clear all filters
- [Layout](../Layout.md) - Root container
