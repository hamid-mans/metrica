import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        url: String,   // <--- ici Stimulus lit data-sortable-url-value
        token: String  // <--- ici Stimulus lit data-sortable-token-value
    }

    connect() {
        console.log("CONNECT")
        console.log("URL:", this.urlValue)
        this.sortable = new Sortable(this.element.querySelector('tbody'), {
            animation: 150,
            handle: '.handle',
            onEnd: () => this.updateDisplayedOrder()
        });
    }

    save(event) {
        console.log("ICI")
        event.preventDefault();

        const rows = this.element.querySelectorAll('tr');

        const order = [];

        rows.forEach(row => {
            order.push(row.dataset.id);
        });

        fetch(this.urlValue, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.tokenValue
            },
            body: JSON.stringify({
                order: order
            })
        })
            .then(r => r.json())
            .then(data => {
                console.log(data);
            });
    }

    updateDisplayedOrder() {
        this.element.querySelectorAll('.order').forEach((cell, index) => {
            cell.textContent = index + 1;
        });
    }
}