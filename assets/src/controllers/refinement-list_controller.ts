import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static values = {
    isShowingMore: {
      type: Boolean,
      default: false,
    },
    showMoreLabel: String,
    showLessLabel: String,
  };

  declare isShowingMoreValue: boolean;
  declare showMoreLabelValue: string;
  declare showLessLabelValue: string;

  static targets = ['toggle'];

  declare hasToggleTarget: boolean;
  declare toggleTarget: HTMLFormElement;

  mutationObserver: MutationObserver;

  initialize() {
    this.mutationObserver = new MutationObserver(this.handleMutation);
  }

  connect() {
    this.mutationObserver.observe(this.element, {
      childList: true,
    });
  }

  private handleMutation = () => {
    this.updateToggleLabel();
  };

  isShowingMoreValueChanged() {
    this.updateToggleLabel();
  }

  toggleShowMore() {
    this.isShowingMoreValue = !this.isShowingMoreValue;
  }

  /**
   * Update the toggle button label based on current state
   * @private
   */
  private updateToggleLabel() {
    if (!this.hasToggleTarget) return;
    this.toggleTarget.innerHTML = this.isShowingMoreValue ? this.showLessLabelValue : this.showMoreLabelValue;
  }

  disconnect() {
    this.mutationObserver.disconnect();
  }
}
