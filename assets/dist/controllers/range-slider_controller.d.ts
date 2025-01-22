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
    submit(): void;
}
