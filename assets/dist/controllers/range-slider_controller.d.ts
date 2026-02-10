import { Controller } from '@hotwired/stimulus';
export default class extends Controller<HTMLElement> {
    static values: {
        precision: {
            type: NumberConstructor;
            default: number;
        };
        leading: {
            type: StringConstructor;
            default: string;
        };
        trailing: {
            type: StringConstructor;
            default: string;
        };
        isReady: {
            type: BooleanConstructor;
            default: boolean;
        };
    };
    precisionValue: number;
    leadingValue: string;
    trailingValue: string;
    isReadyValue: boolean;
    static targets: string[];
    formTarget: HTMLFormElement;
    minInputTarget: HTMLInputElement;
    maxInputTarget: HTMLInputElement;
    hasMinValueTarget: boolean;
    minValueTarget: HTMLElement;
    hasMaxValueTarget: boolean;
    maxValueTarget: HTMLElement;
    formTargetConnected(): void;
    updateFloor: () => void;
    updateCeil: () => void;
    update(method?: 'floor' | 'ceil'): void;
    protected getSliderValues(): {
        min: number;
        max: number;
        step: number;
        minValue: number;
        maxValue: number;
    };
    protected getThumbWidthVariable(): string;
    protected calculatePositions(values: ReturnType<typeof this.getSliderValues>, method: 'floor' | 'ceil'): {
        mid: number;
        range: number;
    };
    protected updateLayout(mid: number, range: number, min: number, max: number, thumbWidthVariable: string): void;
    protected updateGradients(mid: number, min: number, max: number, minValue: number, maxValue: number, thumbWidth: number, thumbWidthUnit: string): void;
    protected updateDisplayedValues(): void;
    protected handleSingleValue(thumbWidthVariable: string): void;
    protected enableInputs(): void;
    protected disableInputs(): void;
    submit(): void;
}
