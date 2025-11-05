import { Controller } from '@hotwired/stimulus';
import { type Component, getComponent } from '@symfony/ux-live-component';

export default class extends Controller {
  static values = {
    isShowingMore: {
      type: Boolean,
      default: false,
    },
    showMoreLabel: String,
    showLessLabel: String,
    sortBy: {
      type: String,
      default: 'count',
    },
    limit: {
      type: Number,
      default: 10,
    },
    property: String,
  };

  declare isShowingMoreValue: boolean;
  declare showMoreLabelValue: string;
  declare showLessLabelValue: string;
  declare sortByValue: string;
  declare limitValue: number;
  declare propertyValue: string;

  static targets = ['toggle', 'item', 'sortSelect', 'input'];

  declare hasToggleTarget: boolean;
  declare toggleTarget: HTMLFormElement;
  declare hasSortSelectTarget: boolean;
  declare sortSelectTarget: HTMLSelectElement;
  declare itemTargets: HTMLElement[];
  declare hasInputTarget: boolean;
  declare inputTarget: HTMLInputElement;

  mutationObserver: MutationObserver;
  private searchQuery = '';
  private liveComponent: Component | null = null;

  async initialize() {
    this.mutationObserver = new MutationObserver(this.handleMutation);

    const liveElement = this.element.closest('[data-live-name-value]') as HTMLElement;
    if (liveElement) {
      try {
        this.liveComponent = await getComponent(liveElement);
      } catch (error) {
        console.error('Failed to initialize LiveComponent:', error);
      }
    }
  }

  connect() {
    this.mutationObserver.observe(this.element, {
      childList: true,
    });

    this.syncDataAttribute();

    if (this.itemTargets.length > 0) {
      this.sortItems();
    }
  }

  private handleMutation = () => {
    this.updateToggleLabel();
  };

  isShowingMoreValueChanged() {
    this.updateToggleLabel();
    this.syncDataAttribute();
    this.updateVisibility();
  }

  toggleShowMore() {
    this.isShowingMoreValue = !this.isShowingMoreValue;
  }

  filter(): void {
    if (!this.hasInputTarget) return;

    this.searchQuery = this.inputTarget.value.toLowerCase().trim();
    this.updateVisibility();
  }

  /**
   * Update the toggle button label based on current state
   * @private
   */
  private updateToggleLabel() {
    if (!this.hasToggleTarget) return;
    this.toggleTarget.innerHTML = this.isShowingMoreValue ? this.showLessLabelValue : this.showMoreLabelValue;
  }

  /**
   * Sync the data attribute that CSS depends on
   * @private
   */
  private syncDataAttribute() {
    this.element.setAttribute('data-ux-search--refinement-list-is-showing-more-value', String(this.isShowingMoreValue));
  }

  disconnect() {
    this.mutationObserver.disconnect();
  }

  changeSort = (event: Event) => {
    if (!this.hasSortSelectTarget) return;

    const select = event.target as HTMLSelectElement;
    this.sortByValue = select.value;
    this.sortItems();
  };

  /**
   * Syncs the sort selection to LiveComponent by calling the action directly
   * via the LiveComponent JavaScript API
   */
  syncLiveAction = async (event: Event) => {
    if (!this.hasSortSelectTarget || !this.liveComponent) return;

    const select = event.target as HTMLSelectElement;

    try {
      await this.liveComponent.action('changeFacetSort', {
        property: this.propertyValue,
        sortBy: select.value,
      });
    } catch (error) {
      console.error('Failed to sync facet sort to LiveComponent:', error);
    }
  };

  sortByValueChanged() {
    this.sortItems();
  }

  private sortItems() {
    const items = Array.from(this.itemTargets);
    const listElement = items[0]?.parentElement;

    if (!listElement) return;

    items.sort((a, b) => {
      const aValue = a.getAttribute('data-facet-value') || '';
      const aCount = parseInt(a.getAttribute('data-facet-count') || '0', 10);
      const bValue = b.getAttribute('data-facet-value') || '';
      const bCount = parseInt(b.getAttribute('data-facet-count') || '0', 10);

      if (this.sortByValue === 'name') {
        return aValue.localeCompare(bValue, undefined, { sensitivity: 'base' });
      } else {
        return bCount - aCount;
      }
    });

    items.forEach((item, index) => {
      listElement.appendChild(item);

      if (index >= this.limitValue) {
        item.classList.add('ux-search-refinement-list__item--exceed-limit');
      } else {
        item.classList.remove('ux-search-refinement-list__item--exceed-limit');
      }
    });

    this.updateVisibility();
  }

  private updateVisibility(): void {
    const hasSearchQuery = this.searchQuery !== '';

    if (hasSearchQuery && !this.isShowingMoreValue) {
      this.isShowingMoreValue = true;
    }

    this.itemTargets.forEach(item => {
      const matchesSearch = this.itemMatchesSearch(item);

      if (!matchesSearch) {
        item.style.display = 'none';
        return;
      }

      item.style.removeProperty('display');
    });

    if (this.hasToggleTarget) {
      this.toggleTarget.style.display = hasSearchQuery ? 'none' : '';
    }
  }

  private itemMatchesSearch(item: HTMLElement): boolean {
    if (this.searchQuery === '') return true;

    const value = item.getAttribute('data-facet-value') || '';
    return this.fuzzyMatch(this.searchQuery, value.toLowerCase());
  }

  private fuzzyMatch(query: string, text: string): boolean {
    let queryIndex = 0;
    let textIndex = 0;

    while (queryIndex < query.length && textIndex < text.length) {
      if (query[queryIndex] === text[textIndex]) {
        queryIndex++;
      }
      textIndex++;
    }

    return queryIndex === query.length;
  }
}
