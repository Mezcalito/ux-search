import { Controller } from '@hotwired/stimulus';
export default class extends Controller {
    static values: {
        isShowingMore: {
            type: BooleanConstructor;
            default: boolean;
        };
        showMoreLabel: StringConstructor;
        showLessLabel: StringConstructor;
    };
    isShowingMoreValue: boolean;
    showMoreLabelValue: string;
    showLessLabelValue: string;
    static targets: string[];
    hasToggleTarget: boolean;
    toggleTarget: HTMLFormElement;
    mutationObserver: MutationObserver;
    initialize(): void;
    connect(): void;
    private handleMutation;
    isShowingMoreValueChanged(): void;
    toggleShowMore(): void;
    private updateToggleLabel;
    disconnect(): void;
}
