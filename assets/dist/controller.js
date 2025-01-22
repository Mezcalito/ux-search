import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';

class controller extends Controller {
    async initialize() {
        this.component = await getComponent(this.element);
    }
    connect() {
        window.addEventListener('history:update', this.handleHistoryUpdate);
    }
    handleHistoryUpdate = (event) => {
        const customEvent = event;
        this.updateUrl(customEvent.detail.url);
    };
    async updateFacetRange(event) {
        const { property, rangeMin, rangeMax } = event.params;
        const form = event.currentTarget;
        const { min, max } = this.getRangeValues(form, property, parseFloat(rangeMin), parseFloat(rangeMax));
        await this.component.action('updateFacetRange', { property, min, max });
    }
    getRangeValues(form, property, rangeMin, rangeMax) {
        const data = new FormData(form);
        const getValue = (suffix) => {
            const value = data.get(`${property}-${suffix}`);
            return value?.length ? Number(value) : null;
        };
        const min = getValue('min');
        const max = getValue('max');
        return {
            min: typeof min === 'number' ? (min > rangeMin ? min : null) : null,
            max: typeof max === 'number' ? (max < rangeMax ? max : null) : null,
        };
    }
    updateUrl(url) {
        history.replaceState(history.state, '', url);
    }
    disconnect() {
        window.removeEventListener('history:update', this.handleHistoryUpdate);
    }
}

export { controller as default };
