import { Controller } from '@hotwired/stimulus';

class default_1 extends Controller {
    static values = {
        isShowingMore: {
            type: Boolean,
            default: false,
        },
        showMoreLabel: String,
        showLessLabel: String,
    };
    static targets = ['toggle'];
    mutationObserver;
    initialize() {
        this.mutationObserver = new MutationObserver(this.handleMutation);
    }
    connect() {
        this.mutationObserver.observe(this.element, {
            childList: true,
        });
    }
    handleMutation = () => {
        this.updateToggleLabel();
    };
    isShowingMoreValueChanged() {
        this.updateToggleLabel();
    }
    toggleShowMore() {
        this.isShowingMoreValue = !this.isShowingMoreValue;
    }
    updateToggleLabel() {
        if (!this.hasToggleTarget)
            return;
        this.toggleTarget.innerHTML = this.isShowingMoreValue ? this.showLessLabelValue : this.showMoreLabelValue;
    }
    disconnect() {
        this.mutationObserver.disconnect();
    }
}

export { default_1 as default };
