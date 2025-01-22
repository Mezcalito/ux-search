import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['searchArea'];

    toggle() {
        this.searchAreaTarget.toggleAttribute('hidden');
        this.searchAreaTarget.classList.toggle('hidden');
    }
}
