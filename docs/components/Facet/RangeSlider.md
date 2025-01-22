# RangeSlider

The `RangeSlider` component provides a user-friendly way to filter the results, based on a single numeric range.

## Default layout

```twig
<fieldset {{ attributes.defaults({
    'class': 'ux-search-facet ux-search-range-slider',
    'data-controller': 'ux-search-range-slider',
    'data-ux-search-range-slider-leading-value': this.leading|default(''),
    'data-ux-search-range-slider-trailing-value': this.trailing|default(''),
    'data-skip-morph': true
}) }}>
    <legend class="ux-search-facet__title">{{ label }}</legend>
    <form
        class="ux-search-range-slider__form"
        data-ux-search-range-slider-target="form"
        data-action="submit->ux-search#updateFacetRange:prevent"
        data-ux-search-property-param="{{ property }}"
        data-ux-search-range-min-param="{{ facetStat.min }}"
        data-ux-search-range-max-param="{{ facetStat.max }}"
    >
        <input
            id="{{ property }}-min"
            name="{{ property }}-min"
            aria-label="Minimum {{ property }}"
            aria-describedby="{{ property }}-min-value"
            class="ux-search-range-slider__input ux-search-range-slider__input--min"
            type="range"
            min="{{ facetStat.min }}"
            max="{{ facetStat.max }}"
            placeholder="{{ facetStat.min }}"
            value="{{ (facetStat.userMin is not null) ? facetStat.userMin : facetStat.min }}"
            step="{{ this.step }}"
            data-ux-search-range-slider-target="minInput"
            data-action="
                input->ux-search-range-slider#updateCeil
                focus->ux-search-range-slider#updateCeil
                mousedown->ux-search-range-slider#updateCeil
                touchstart->ux-search-range-slider#updateCeil
                change->ux-search-range-slider#submit
            "
        />
        <input
            id="{{ property }}-max"
            name="{{ property }}-max"
            aria-label="Maximum {{ property }}"
            aria-describedby="{{ property }}-max-value"
            class="ux-search-range-slider__input ux-search-range-slider__input--max"
            type="range"
            min="{{ facetStat.min }}"
            max="{{ facetStat.max }}"
            placeholder="{{ facetStat.max }}"
            value="{{ (facetStat.userMax is not null) ? facetStat.userMax : facetStat.max }}"
            step="{{ this.step }}"
            data-ux-search-range-slider-target="maxInput"
            data-action="
                input->ux-search-range-slider#updateFloor
                focus->ux-search-range-slider#updateFloor
                mousedown->ux-search-range-slider#updateFloor
                touchstart->ux-search-range-slider#updateFloor
                change->ux-search-range-slider#submit
            "
        />
    </form>
    <div class="ux-search-range-slider__values">
        <span
            id="{{ property }}-min-value"
            class="ux-search-range-slider__value ux-search-range-slider__value--min"
            data-ux-search-range-slider-target="minValue"
        >
            {{ this.leading ~ facetStat.userMin|default(facetStat.min) ~ this.trailing }}
        </span>
        <span
            id="{{ property }}-max-value"
            class="ux-search-range-slider__value ux-search-range-slider__value--max"
            data-ux-search-range-slider-target="maxValue"
        >
            {{ this.leading ~ facetStat.userMax|default(facetStat.max) ~ this.trailing }}
        </span>
    </div>
</fieldset>
```

## Default HTML output
```html
<fieldset class="ux-search-facet ux-search-range-slider" data-controller="ux-search-range-slider" data-ux-search-range-slider-leading-value="" data-ux-search-range-slider-trailing-value="" data-skip-morph="" style="--ux-search-range-slider-min-gradient-position: calc(0% + 0.625rem); --ux-search-range-slider-max-gradient-position: calc(99% + -0.625rem);" data-ux-search-range-slider-is-ready-value="true">
    <legend class="ux-search-facet__title">Price</legend>
    <form class="ux-search-range-slider__form" data-ux-search-range-slider-target="form" data-action="submit->ux-search#updateFacetRange:prevent" data-ux-search-property-param="price" data-ux-search-range-min-param="1.99" data-ux-search-range-max-param="4999.98">
        <input id="price-min" name="price-min" aria-label="Minimum price" aria-describedby="price-min-value" class="ux-search-range-slider__input ux-search-range-slider__input--min" type="range" min="1.99" max="2500.990" placeholder="1.99" value="1.99" step="1" data-ux-search-range-slider-target="minInput" data-action="
            input->ux-search-range-slider#updateCeil
            focus->ux-search-range-slider#updateCeil
            mousedown->ux-search-range-slider#updateCeil
            touchstart->ux-search-range-slider#updateCeil
            change->ux-search-range-slider#submit" 
        style="flex-basis: calc(50% + 1.25rem);">
        <input id="price-max" name="price-max" aria-label="Maximum price" aria-describedby="price-max-value" class="ux-search-range-slider__input ux-search-range-slider__input--max" type="range" min="2500.990" max="4999.98" placeholder="4999.98" value="4999.98" step="1" data-ux-search-range-slider-target="maxInput" data-action="
            input->ux-search-range-slider#updateFloor
            focus->ux-search-range-slider#updateFloor
            mousedown->ux-search-range-slider#updateFloor
            touchstart->ux-search-range-slider#updateFloor
            change->ux-search-range-slider#submit" 
        style="flex-basis: calc(49% + 1.25rem);">
    </form>
    <div class="ux-search-range-slider__values">
        <span id="price-min-value" class="ux-search-range-slider__value ux-search-range-slider__value--min" data-ux-search-range-slider-target="minValue">1.99</span>
        <span id="price-max-value" class="ux-search-range-slider__value ux-search-range-slider__value--max" data-ux-search-range-slider-target="maxValue">4998.99</span>
    </div>
</fieldset>
```
