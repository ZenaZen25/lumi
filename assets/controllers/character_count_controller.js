import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'counter'];

    static values = {
        max: { type: Number, default: 150 }
    };

    connect() {
        this.updateCount();
    }

    updateCount() {
        const remaining = this.maxValue - this.inputTarget.value.length;

        this.counterTarget.textContent = remaining;

        this.counterTarget.classList.toggle(
            'counter--danger',
            remaining < 20
        );
    }
}