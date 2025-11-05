import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';

class default_1 extends Controller {
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
    static targets = ['toggle', 'item', 'sortSelect', 'input'];
    mutationObserver;
    searchQuery = '';
    liveComponent = null;
    async initialize() {
        this.mutationObserver = new MutationObserver(this.handleMutation);
        const liveElement = this.element.closest('[data-live-name-value]');
        if (liveElement) {
            try {
                this.liveComponent = await getComponent(liveElement);
            }
            catch (error) {
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
    handleMutation = () => {
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
    filter() {
        if (!this.hasInputTarget)
            return;
        this.searchQuery = this.inputTarget.value.toLowerCase().trim();
        this.updateVisibility();
    }
    updateToggleLabel() {
        if (!this.hasToggleTarget)
            return;
        this.toggleTarget.innerHTML = this.isShowingMoreValue ? this.showLessLabelValue : this.showMoreLabelValue;
    }
    syncDataAttribute() {
        this.element.setAttribute('data-ux-search--refinement-list-is-showing-more-value', String(this.isShowingMoreValue));
    }
    disconnect() {
        this.mutationObserver.disconnect();
    }
    changeSort = (event) => {
        if (!this.hasSortSelectTarget)
            return;
        const select = event.target;
        this.sortByValue = select.value;
        this.sortItems();
    };
    syncLiveAction = async (event) => {
        if (!this.hasSortSelectTarget || !this.liveComponent)
            return;
        const select = event.target;
        try {
            await this.liveComponent.action('changeFacetSort', {
                property: this.propertyValue,
                sortBy: select.value,
            });
        }
        catch (error) {
            console.error('Failed to sync facet sort to LiveComponent:', error);
        }
    };
    sortByValueChanged() {
        this.sortItems();
    }
    sortItems() {
        const items = Array.from(this.itemTargets);
        const listElement = items[0]?.parentElement;
        if (!listElement)
            return;
        items.sort((a, b) => {
            const aValue = a.getAttribute('data-facet-value') || '';
            const aCount = parseInt(a.getAttribute('data-facet-count') || '0', 10);
            const bValue = b.getAttribute('data-facet-value') || '';
            const bCount = parseInt(b.getAttribute('data-facet-count') || '0', 10);
            if (this.sortByValue === 'name') {
                return aValue.localeCompare(bValue, undefined, { sensitivity: 'base' });
            }
            else {
                return bCount - aCount;
            }
        });
        items.forEach((item, index) => {
            listElement.appendChild(item);
            if (index >= this.limitValue) {
                item.classList.add('ux-search-refinement-list__item--exceed-limit');
            }
            else {
                item.classList.remove('ux-search-refinement-list__item--exceed-limit');
            }
        });
        this.updateVisibility();
    }
    updateVisibility() {
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
    itemMatchesSearch(item) {
        if (this.searchQuery === '')
            return true;
        const value = item.getAttribute('data-facet-value') || '';
        return this.fuzzyMatch(this.searchQuery, value.toLowerCase());
    }
    fuzzyMatch(query, text) {
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

export { default_1 as default };
