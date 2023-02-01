class OrderLabel {
    constructor() {
        this.toggleBtn = document.querySelectorAll('.open-order-row');
        this.weightAndQuantityInput = null;
        this.generateLabelBtn = null;
        this.deleteLabelBtn = null;
        this.typingTimer = null;
        this.runningTimer = null;

        this.init();
    }
    init() {
        let t = this;
        this.weightAndQuantityInput = document.querySelectorAll('.change-order-item input');
        this.generateLabelBtn = document.querySelectorAll('.generate-label-btn');

        this.toggleBtn.forEach(btn=>{
            btn.addEventListener('click', function (e) {
                let tr = e.target.closest('tr');
                // prevent double running
                clearTimeout(this.runningTimer);
                this.runningTimer = setTimeout(function () {
                    t.toggleOrderDetails(tr);
                }, 100);

            })
        });
        this.weightAndQuantityInput.forEach(input=>{
            input.addEventListener('input', function (e) {
                t.updateWeightOrQuantity(e.target);
            })
        })
        this.generateLabelBtn.forEach(btn=>{
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                // prevent double running
                clearTimeout(this.runningTimer);
                this.runningTimer = setTimeout(function () {
                    t.generateLabel(e.target.closest('form'));
                }, 100);
            })
        })
    }
    toggleOrderDetails(tr) {
        let orderNumber = tr.querySelector('[data-render-order-url]').dataset.number;

        // create order details once instead of at every clic
        if (!tr.classList.contains('loaded')) {
            this.createOrderDetails(tr);
        }

        let orderDetailsRow = document.querySelector('[data-order-number="'+orderNumber+'"]');
        let i = tr.querySelector('[data-render-order-url] > i');
        if (tr.classList.contains('active')) {
            tr.classList.remove('active');
            orderDetailsRow.style.display = "none";
            this.toggleIcon(i, "plus");
        }
        else {
            tr.classList.add('active');
            orderDetailsRow.style.display = "table-row";
            this.toggleIcon(i, "minus");
        }
    }
    createOrderDetails(tr) {
        let t = this;
        let url = tr.querySelector('[data-render-order-url]').dataset.renderOrderUrl;
        let orderNumber = tr.querySelector('[data-render-order-url]').dataset.number;
        let newTr = document.createElement('tr');
        let newTd = document.createElement('td');
        let countTds = tr.querySelectorAll('td').length;

        newTr.dataset.orderNumber = orderNumber;
        newTr.append(newTd);
        newTd.setAttribute('colspan', countTds);
        tr.after(newTr);

        $.ajax({
            url: url,
            type: "GET",
            success: function (response) {
                if (response.html && response.html !== "") {
                    newTd.innerHTML = response.html;
                    tr.classList.add('loaded');
                    tr.classList.add('active');
                    t.renderLabelSummary(newTd.querySelector('.order-details-container'));
                    t.init();
                }
            },
            error: function () {
                alert("error");
            }
        });
    }
    toggleIcon(i, value) {
        i.classList.remove('plus', 'minus');
        i.classList.add(value)
    }
    updateWeightOrQuantity(input) {
        // update quantity or weight when done typing
        clearTimeout(this.typingTimer);
        let t = this;
        this.typingTimer = setTimeout(function () {
            t.positiveInput(input);
            t.updateTotalWeight(input.closest('form'));
        }, 300);
    }
    positiveInput(input) {
        // value can't be under 0
        if (input.value < 0)
            input.value = 0;
    }
    updateTotalWeight(form) {
        let datas = new FormData(form);
        let orderItemIds = [];
        let totalWeight = 0;
        for (var key of datas.keys()) {
            orderItemIds.push(key.replace('quantity_', '').replace('weight_', ''));
        }
        orderItemIds = [...new Set(orderItemIds)]; // array unique
        orderItemIds.forEach(item=>{
            totalWeight += (datas.get('quantity_'+item) * datas.get('weight_'+item))
        })
        form.querySelector('.total-weight').value = totalWeight;
    }
    generateLabel(form) {
        let t = this;
        let orderNumber = form.closest('[data-order-number]').dataset.orderNumber;
        let transporterId = document.querySelector('button[data-number="'+orderNumber+'"]').dataset.transporter;
        let formData = new FormData(form);
        formData.append("transporter", transporterId);
        if (formData.get('total_weight') === 0 || formData.get('total_weight') === "") {
            alert("Veuillez renseigner un poids total pour l'envoi");
        }
        else {
            $.ajax({
                url: form.getAttribute('action'),
                type: form.getAttribute('method'),
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    t.renderLabelSummary(form.closest('.order-details-container'));
                },
                error: function (response) {
                    alert(response.responseJson ? response.responseJson : "error" );
                }
            });
        }
    }
    renderLabelSummary(orderDetailsContainer) {
        let t = this;
        let url = orderDetailsContainer.dataset.labelSummaryUrl;
        let container = orderDetailsContainer.querySelector('.label-summary');
        $.ajax({
            url: url,
            type: "GET",
            success: function (response) {
                if (response.html && response.html !== "") {
                    container.innerHTML = response.html;
                    this.deleteLabelBtn = document.querySelectorAll('.delete-label-btn');
                    this.deleteLabelBtn.forEach(btn=>{
                        btn.addEventListener('click', function (e) {
                            e.preventDefault();
                            t.deleteLabel(e.target);
                        })
                    })
                }
            },
            error: function () {
                alert("error");
            }
        });
    }
    deleteLabel(deleteBtn) {
        let t = this;
        if (!deleteBtn.classList.contains('button'))
            deleteBtn = deleteBtn.parentElement;

        $.ajax({
            url: deleteBtn.dataset.deleteUrl,
            type: "GET",
            success: function () {
                // clear summary before update it
                let container = deleteBtn.closest('.label-summary');
                container.innerHTML = "";
                t.renderLabelSummary(container.closest('.order-details-container'));
                t.init();
            },
            error: function () {
                alert("error");
            }
        });
    }
}

document.addEventListener("DOMContentLoaded", function() {
    const $OrderLabel = new OrderLabel();
});
