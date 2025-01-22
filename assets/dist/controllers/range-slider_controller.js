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
        const min = parseFloat(this.minInputTarget.min);
        const max = parseFloat(this.maxInputTarget.max);
        const step = parseFloat(this.minInputTarget.step);
        const minValue = parseFloat(this.minInputTarget.value);
        const maxValue = parseFloat(this.maxInputTarget.value);
        const midValue = (maxValue - minValue) / 2;
        const mid = minValue + Math[method](midValue / step) * step;
        const range = max - min;
        const thumbWidthVariable = getComputedStyle(this.minInputTarget).getPropertyValue('--ux-search-range-slider-thumb-width');
        const thumbWidth = parseFloat(thumbWidthVariable);
        const thumbWidthUnit = thumbWidthVariable.replace(/^[\d.]+/, '');
        const leftWidth = ((mid - min) / range) * 100;
        const rightWidth = ((max - mid) / range) * 100;
        this.minInputTarget.style.flexBasis = `calc(${leftWidth}% + ${thumbWidthVariable})`;
        this.maxInputTarget.style.flexBasis = `calc(${rightWidth}% + ${thumbWidthVariable})`;
        this.minInputTarget.max = mid.toFixed(this.precisionValue);
        this.maxInputTarget.min = mid.toFixed(this.precisionValue);
        const minFill = (minValue - min) / (mid - min) || 0;
        const maxFill = (maxValue - mid) / (max - mid) || 0;
        const minFillThumb = ((0.5 - minFill) * thumbWidth).toFixed(this.precisionValue);
        const maxFillThumb = ((0.5 - maxFill) * thumbWidth).toFixed(this.precisionValue);
        this.element.style.setProperty('--ux-search-range-slider-min-gradient-position', `calc(${(minFill * 100).toFixed(this.precisionValue)}% + ${minFillThumb}${thumbWidthUnit})`);
        this.element.style.setProperty('--ux-search-range-slider-max-gradient-position', `calc(${(maxFill * 100).toFixed(this.precisionValue)}% + ${maxFillThumb}${thumbWidthUnit})`);
        if (this.hasMinValueTarget) {
            this.minValueTarget.innerHTML = `${this.leadingValue}${this.minInputTarget.value}${this.trailingValue}`;
        }
        if (this.hasMaxValueTarget) {
            this.maxValueTarget.innerHTML = `${this.leadingValue}${this.maxInputTarget.value}${this.trailingValue}`;
        }
    }
    submit() {
        this.formTarget.requestSubmit();
    }
}

export { default_1 as default };
