# RangeInput

The `RangeInput` component displays allows a user to select a numeric range using a minimum and maximum input.

## Block available

| name   | Description |
|--------|-------------|
| label  | -           |
| form   | -           |
| submit | -           |

## Default layout

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

## Default HTML output
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
