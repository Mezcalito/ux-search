import { Controller } from '@hotwired/stimulus';

class default_1 extends Controller {
    static values = {
        precision: {
            type: Number,
            default: 3,
        },
        leading: {
            type: String,
            default: '',
        },
        trailing: {
            type: String,
            default: '',
        },
        isReady: {
            type: Boolean,
            default: false,
        },
    };
    static targets = ['form', 'minInput', 'maxInput', 'minValue', 'maxValue'];
    formTargetConnected() {
        this.update();
        this.isReadyValue = true;
    }
    updateFloor = () => this.update('floor');
    updateCeil = () => this.update('ceil');
    update(method = 'ceil') {
        const values = this.getSliderValues();
        const { min, max } = values;
        const thumbWidthVariable = this.getThumbWidthVariable();
        const thumbWidth = parseFloat(thumbWidthVariable);
        const thumbWidthUnit = thumbWidthVariable.replace(/^[\d.]+/, '');
        if (min === max) {
            this.handleSingleValue(thumbWidthVariable);
            this.updateDisplayedValues();
            return;
        }
        this.enableInputs();
        const { mid, range } = this.calculatePositions(values, method);
        this.updateLayout(mid, range, min, max, thumbWidthVariable);
        this.updateGradients(mid, min, max, values.minValue, values.maxValue, thumbWidth, thumbWidthUnit);
        this.updateDisplayedValues();
    }
    getSliderValues() {
        return {
            min: parseFloat(this.minInputTarget.min),
            max: parseFloat(this.maxInputTarget.max),
            step: parseFloat(this.minInputTarget.step),
            minValue: parseFloat(this.minInputTarget.value),
            maxValue: parseFloat(this.maxInputTarget.value),
        };
    }
    getThumbWidthVariable() {
        return getComputedStyle(this.minInputTarget).getPropertyValue('--ux-search-range-slider-thumb-width');
    }
    calculatePositions(values, method) {
        const { min, max, step, minValue, maxValue } = values;
        const midValue = (maxValue - minValue) / 2;
        const mid = minValue + Math[method](midValue / step) * step;
        const range = max - min;
        return { mid, range };
    }
    updateLayout(mid, range, min, max, thumbWidthVariable) {
        const leftWidth = ((mid - min) / range) * 100;
        const rightWidth = ((max - mid) / range) * 100;
        this.minInputTarget.style.flexBasis = `calc(${leftWidth}% + ${thumbWidthVariable})`;
        this.maxInputTarget.style.flexBasis = `calc(${rightWidth}% + ${thumbWidthVariable})`;
        this.minInputTarget.max = mid.toFixed(this.precisionValue);
        this.maxInputTarget.min = mid.toFixed(this.precisionValue);
    }
    updateGradients(mid, min, max, minValue, maxValue, thumbWidth, thumbWidthUnit) {
        const minFill = (minValue - min) / (mid - min) || 0;
        const maxFill = (maxValue - mid) / (max - mid) || 0;
        const minFillThumb = ((0.5 - minFill) * thumbWidth).toFixed(this.precisionValue);
        const maxFillThumb = ((0.5 - maxFill) * thumbWidth).toFixed(this.precisionValue);
        this.element.style.setProperty('--ux-search-range-slider-min-gradient-position', `calc(${(minFill * 100).toFixed(this.precisionValue)}% + ${minFillThumb}${thumbWidthUnit})`);
        this.element.style.setProperty('--ux-search-range-slider-max-gradient-position', `calc(${(maxFill * 100).toFixed(this.precisionValue)}% + ${maxFillThumb}${thumbWidthUnit})`);
    }
    updateDisplayedValues() {
        if (this.hasMinValueTarget) {
            this.minValueTarget.innerHTML = `${this.leadingValue}${this.minInputTarget.value}${this.trailingValue}`;
        }
        if (this.hasMaxValueTarget) {
            this.maxValueTarget.innerHTML = `${this.leadingValue}${this.maxInputTarget.value}${this.trailingValue}`;
        }
    }
    handleSingleValue(thumbWidthVariable) {
        this.minInputTarget.style.flexBasis = `calc(100% + ${thumbWidthVariable})`;
        this.maxInputTarget.style.flexBasis = `calc(0% + ${thumbWidthVariable})`;
        this.element.style.setProperty('--ux-search-range-slider-min-gradient-position', '0%');
        this.element.style.setProperty('--ux-search-range-slider-max-gradient-position', '100%');
        this.disableInputs();
    }
    enableInputs() {
        this.minInputTarget.disabled = false;
        this.maxInputTarget.disabled = false;
    }
    disableInputs() {
        this.minInputTarget.disabled = true;
        this.maxInputTarget.disabled = true;
    }
    submit() {
        this.formTarget.requestSubmit();
    }
}

export { default_1 as default };
