import { Controller } from '@hotwired/stimulus';
export default class extends Controller {
    static values: {
        isShowingMore: {
            type: BooleanConstructor;
            default: boolean;
        };
        showMoreLabel: StringConstructor;
        showLessLabel: StringConstructor;
        sortBy: {
            type: StringConstructor;
            default: string;
        };
        limit: {
            type: NumberConstructor;
            default: number;
        };
        property: StringConstructor;
    };
    isShowingMoreValue: boolean;
    showMoreLabelValue: string;
    showLessLabelValue: string;
    sortByValue: string;
    limitValue: number;
    propertyValue: string;
    static targets: string[];
    hasToggleTarget: boolean;
    toggleTarget: HTMLFormElement;
    hasSortSelectTarget: boolean;
    sortSelectTarget: HTMLSelectElement;
    itemTargets: HTMLElement[];
    hasInputTarget: boolean;
    inputTarget: HTMLInputElement;
    mutationObserver: MutationObserver;
    private searchQuery;
    private liveComponent;
    initialize(): Promise<void>;
    connect(): void;
    private handleMutation;
    isShowingMoreValueChanged(): void;
    toggleShowMore(): void;
    filter(): void;
    private updateToggleLabel;
    private syncDataAttribute;
    disconnect(): void;
    changeSort: (event: Event) => void;
    syncLiveAction: (event: Event) => Promise<void>;
    sortByValueChanged(): void;
    private sortItems;
    private updateVisibility;
    private itemMatchesSearch;
    private fuzzyMatch;
}
