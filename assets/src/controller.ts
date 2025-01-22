import { type ActionEvent, Controller } from '@hotwired/stimulus';
import { type Component, getComponent } from '@symfony/ux-live-component';
import './styles/default.scss';

export default class extends Controller<HTMLElement> {
  declare component: Component;

  async initialize() {
    this.component = await getComponent(this.element);
  }

  connect() {
    window.addEventListener('history:update', this.handleHistoryUpdate);
  }

  private handleHistoryUpdate = (event: Event) => {
    const customEvent = event as CustomEvent<{ url: string }>;
    this.updateUrl(customEvent.detail.url);
  };

  async updateFacetRange(event: SubmitEvent & ActionEvent) {
    const { property, rangeMin, rangeMax } = event.params;

    const form = event.currentTarget as HTMLFormElement;
    const { min, max } = this.getRangeValues(form, property, parseFloat(rangeMin), parseFloat(rangeMax));

    await this.component.action('updateFacetRange', { property, min, max });
  }

  private getRangeValues(form: HTMLFormElement, property: string, rangeMin: number, rangeMax: number) {
    const data = new FormData(form);

    const getValue = (suffix: string) => {
      const value = data.get(`${property}-${suffix}`) as string | null;
      return value?.length ? Number(value) : null;
    };

    const min = getValue('min');
    const max = getValue('max');

    // Ensure values are within allowed range
    return {
      min: typeof min === 'number' ? (min > rangeMin ? min : null) : null,
      max: typeof max === 'number' ? (max < rangeMax ? max : null) : null,
    };
  }

  updateUrl(url: string) {
    history.replaceState(history.state, '', url);
  }

  disconnect() {
    window.removeEventListener('history:update', this.handleHistoryUpdate);
  }
}
