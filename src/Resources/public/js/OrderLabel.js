class OrderLabel {
    constructor() {
        this.typingTimer = null;
        this.pendingRequests = new Set();

        this.init();
    }
    init() {
        // event delegation: order details and label summaries are loaded with ajax
        document.addEventListener('click', (e) => {
            const toggleBtn = e.target.closest('.open-order-row');
            if (toggleBtn) {
                e.preventDefault();
                this.toggleOrderDetails(toggleBtn.closest('tr'));
                return;
            }
            const deleteBtn = e.target.closest('.delete-label-btn');
            if (deleteBtn) {
                e.preventDefault();
                this.deleteLabel(deleteBtn);
            }
        });
        document.addEventListener('submit', (e) => {
            const form = e.target.closest('form[name="updated-order"]');
            if (form) {
                e.preventDefault();
                this.generateLabel(form);
            }
        });
        document.addEventListener('input', (e) => {
            if (e.target.closest('.change-order-item')) {
                this.updateWeightOrQuantity(e.target);
            }
        });
    }
    request(url, options = {}) {
        options.headers = Object.assign({'X-Requested-With': 'XMLHttpRequest'}, options.headers || {});

        return fetch(url, options).then((response) => {
            return response.json().catch(() => null).then((data) => {
                if (!response.ok) {
                    throw new Error(typeof data === 'string' ? data : 'error');
                }

                return data;
            });
        });
    }
    // prevent double running
    once(key, callback) {
        if (this.pendingRequests.has(key)) {
            return;
        }
        this.pendingRequests.add(key);
        callback().catch((error) => alert(error.message)).finally(() => this.pendingRequests.delete(key));
    }
    toggleOrderDetails(tr) {
        const button = tr.querySelector('[data-render-order-url]');
        const orderNumber = button.dataset.number;

        // create order details once instead of at every click
        if (!tr.classList.contains('loaded')) {
            this.createOrderDetails(tr);
            return;
        }

        const orderDetailsRow = document.querySelector('tr[data-order-number="' + orderNumber + '"]');
        const opened = !tr.classList.contains('active');
        tr.classList.toggle('active', opened);
        orderDetailsRow.style.display = opened ? 'table-row' : 'none';
        this.toggleIcon(button, opened);
    }
    createOrderDetails(tr) {
        const button = tr.querySelector('[data-render-order-url]');
        const orderNumber = button.dataset.number;

        this.once('details-' + orderNumber, () => {
            return this.request(button.dataset.renderOrderUrl).then((response) => {
                if (!response || !response.html) {
                    return;
                }
                const newTr = document.createElement('tr');
                const newTd = document.createElement('td');
                newTr.dataset.orderNumber = orderNumber;
                newTd.setAttribute('colspan', tr.querySelectorAll('td').length);
                newTd.innerHTML = response.html;
                newTr.append(newTd);
                tr.after(newTr);

                tr.classList.add('loaded', 'active');
                this.toggleIcon(button, true);
                this.updateTotalWeight(newTd.querySelector('form[name="updated-order"]'));

                return this.renderLabelSummary(newTd.querySelector('.order-details-container'));
            });
        });
    }
    toggleIcon(button, opened) {
        button.querySelector('.icon-plus').classList.toggle('d-none', opened);
        button.querySelector('.icon-minus').classList.toggle('d-none', !opened);
    }
    updateWeightOrQuantity(input) {
        // update quantity or weight when done typing
        clearTimeout(this.typingTimer);
        this.typingTimer = setTimeout(() => {
            this.positiveInput(input);
            this.updateTotalWeight(input.closest('form'));
        }, 300);
    }
    positiveInput(input) {
        // value can't be under 0
        if (input.value < 0)
            input.value = 0;
    }
    updateTotalWeight(form) {
        if (!form) {
            return;
        }
        let totalWeight = 0;
        form.querySelectorAll('input[name^="weight_"]').forEach((weightInput) => {
            const itemId = weightInput.name.replace('weight_', '');
            const quantityInput = form.querySelector('input[name="quantity_' + itemId + '"]');
            totalWeight += (parseFloat(quantityInput.value) || 0) * (parseFloat(weightInput.value) || 0);
        });
        form.querySelector('.total-weight').value = Math.round(totalWeight * 1000) / 1000;
    }
    generateLabel(form) {
        const orderNumber = form.closest('[data-order-number]').dataset.orderNumber;
        const transporterId = document.querySelector('button[data-number="' + orderNumber + '"]').dataset.transporter;
        const formData = new FormData(form);
        formData.append('transporter', transporterId);
        if (!parseFloat(formData.get('total_weight'))) {
            alert("Veuillez renseigner un poids total pour l'envoi.");
            return;
        }

        this.once('generate-' + orderNumber, () => {
            return this.request(form.getAttribute('action'), {method: 'POST', body: formData})
                .then(() => this.renderLabelSummary(form.closest('.order-details-container')));
        });
    }
    renderLabelSummary(orderDetailsContainer) {
        const container = orderDetailsContainer.querySelector('.label-summary');

        return this.request(orderDetailsContainer.dataset.labelSummaryUrl).then((response) => {
            if (response && response.html) {
                container.innerHTML = response.html;
            }
        });
    }
    deleteLabel(deleteBtn) {
        const body = new FormData();
        body.append('_token', deleteBtn.dataset.token);
        const orderDetailsContainer = deleteBtn.closest('.order-details-container');

        this.once(deleteBtn.dataset.deleteUrl, () => {
            return this.request(deleteBtn.dataset.deleteUrl, {method: 'POST', body: body})
                .then(() => this.renderLabelSummary(orderDetailsContainer));
        });
    }
}

document.addEventListener("DOMContentLoaded", function() {
    new OrderLabel();
});
