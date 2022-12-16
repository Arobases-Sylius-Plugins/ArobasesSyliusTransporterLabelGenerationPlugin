document.addEventListener("DOMContentLoaded", function() {

    /********************************** *** DATAS *** ************************************/
    let updateShippingFormContainers = document.querySelectorAll('.update-shipping-method-form');

    /********************************** *** MAIN *** ************************************/
    updateShippingFormContainers.forEach(container=>{
        let options = container.querySelectorAll('option');
        hideOtherTransporterProducts(container, options);
        selectOption(container, options);
    })

});

/********************************** *** FUNCTIONS *** ************************************/

function hideOtherTransporterProducts(container, options) {
    let transporterName = container.dataset.transporterName;
    options.forEach(option=>{
        if (!option.textContent.toLowerCase().includes(transporterName.toLowerCase()))
            option.style.display = "none";
    })
}

function selectOption(container, options) {
    let transporterCode = container.closest('[data-transporter-code]').dataset.transporterCode;
    options.forEach(option=>{
        if (option.value === transporterCode)
            option.setAttribute('selected', true)
    })
}