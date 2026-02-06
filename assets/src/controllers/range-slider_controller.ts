import { Controller } from '@hotwired/stimulus';

export default class extends Controller<HTMLElement> {
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

  declare precisionValue: number;
  declare leadingValue: string;
  declare trailingValue: string;
  declare isReadyValue: boolean;

  static targets = ['form', 'minInput', 'maxInput', 'minValue', 'maxValue'];

  declare formTarget: HTMLFormElement;
  declare minInputTarget: HTMLInputElement;
  declare maxInputTarget: HTMLInputElement;
  declare hasMinValueTarget: boolean;
  declare minValueTarget: HTMLElement;
  declare hasMaxValueTarget: boolean;
  declare maxValueTarget: HTMLElement;

  formTargetConnected() {
    this.update();
    this.isReadyValue = true;
  }

  updateFloor = () => this.update('floor');

  updateCeil = (): void => this.update('ceil');

  update(method: 'floor' | 'ceil' = 'ceil') {
    const values = this.getSliderValues();
    const { min, max } = values;

    const thumbWidthVariable = this.getThumbWidthVariable();
    const thumbWidth = parseFloat(thumbWidthVariable);
    const thumbWidthUnit = thumbWidthVariable.replace(/^[\d.]+/, '');

    // Handle edge case: min === max (single value, no range)
    if (min === max) {
      this.handleSingleValue(thumbWidthVariable);
      this.updateDisplayedValues();
      return;
    }

    // Re-enable inputs if they were disabled
    this.enableInputs();

    // Calculate positions
    const { mid, range } = this.calculatePositions(values, method);

    // Update layout
    this.updateLayout(mid, range, min, max, thumbWidthVariable);

    // Update gradients
    this.updateGradients(mid, min, max, values.minValue, values.maxValue, thumbWidth, thumbWidthUnit);

    // Update displayed values
    this.updateDisplayedValues();
  }

  protected getSliderValues() {
    return {
      min: parseFloat(this.minInputTarget.min),
      max: parseFloat(this.maxInputTarget.max),
      step: parseFloat(this.minInputTarget.step),
      minValue: parseFloat(this.minInputTarget.value),
      maxValue: parseFloat(this.maxInputTarget.value),
    };
  }

  protected getThumbWidthVariable(): string {
    return getComputedStyle(this.minInputTarget).getPropertyValue('--ux-search-range-slider-thumb-width');
  }

  protected calculatePositions(
    values: ReturnType<typeof this.getSliderValues>,
    method: 'floor' | 'ceil',
  ): { mid: number; range: number } {
    const { min, max, step, minValue, maxValue } = values;
    const midValue = (maxValue - minValue) / 2;
    const mid = minValue + Math[method](midValue / step) * step;
    const range = max - min;

    return { mid, range };
  }

  protected updateLayout(mid: number, range: number, min: number, max: number, thumbWidthVariable: string) {
    const leftWidth = ((mid - min) / range) * 100;
    const rightWidth = ((max - mid) / range) * 100;

    this.minInputTarget.style.flexBasis = `calc(${leftWidth}% + ${thumbWidthVariable})`;
    this.maxInputTarget.style.flexBasis = `calc(${rightWidth}% + ${thumbWidthVariable})`;

    this.minInputTarget.max = mid.toFixed(this.precisionValue);
    this.maxInputTarget.min = mid.toFixed(this.precisionValue);
  }

  protected updateGradients(
    mid: number,
    min: number,
    max: number,
    minValue: number,
    maxValue: number,
    thumbWidth: number,
    thumbWidthUnit: string,
  ) {
    const minFill = (minValue - min) / (mid - min) || 0;
    const maxFill = (maxValue - mid) / (max - mid) || 0;

    const minFillThumb = ((0.5 - minFill) * thumbWidth).toFixed(this.precisionValue);
    const maxFillThumb = ((0.5 - maxFill) * thumbWidth).toFixed(this.precisionValue);

    this.element.style.setProperty(
      '--ux-search-range-slider-min-gradient-position',
      `calc(${(minFill * 100).toFixed(this.precisionValue)}% + ${minFillThumb}${thumbWidthUnit})`,
    );
    this.element.style.setProperty(
      '--ux-search-range-slider-max-gradient-position',
      `calc(${(maxFill * 100).toFixed(this.precisionValue)}% + ${maxFillThumb}${thumbWidthUnit})`,
    );
  }

  protected updateDisplayedValues() {
    if (this.hasMinValueTarget) {
      this.minValueTarget.innerHTML = `${this.leadingValue}${this.minInputTarget.value}${this.trailingValue}`;
    }
    if (this.hasMaxValueTarget) {
      this.maxValueTarget.innerHTML = `${this.leadingValue}${this.maxInputTarget.value}${this.trailingValue}`;
    }
  }

  protected handleSingleValue(thumbWidthVariable: string) {
    // Place handlers at edges
    this.minInputTarget.style.flexBasis = `calc(100% + ${thumbWidthVariable})`;
    this.maxInputTarget.style.flexBasis = `calc(0% + ${thumbWidthVariable})`;

    // Fill gradient completely
    this.element.style.setProperty('--ux-search-range-slider-min-gradient-position', '0%');
    this.element.style.setProperty('--ux-search-range-slider-max-gradient-position', '100%');

    // Disable inputs to prevent interaction
    this.disableInputs();
  }

  protected enableInputs() {
    this.minInputTarget.disabled = false;
    this.maxInputTarget.disabled = false;
  }

  protected disableInputs() {
    this.minInputTarget.disabled = true;
    this.maxInputTarget.disabled = true;
  }

  submit() {
    this.formTarget.requestSubmit();
  }
}
