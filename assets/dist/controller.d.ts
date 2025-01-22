import { type ActionEvent, Controller } from '@hotwired/stimulus';
import { type Component } from '@symfony/ux-live-component';
import './styles/default.scss';
export default class extends Controller<HTMLElement> {
    component: Component;
    initialize(): Promise<void>;
    connect(): void;
    private handleHistoryUpdate;
    updateFacetRange(event: SubmitEvent & ActionEvent): Promise<void>;
    private getRangeValues;
    updateUrl(url: string): void;
    disconnect(): void;
}
