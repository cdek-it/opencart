$(document).ready(function() {
    let internationalShippingCheckbox = $('#cdek_official_seller_international_shipping_checkbox');
    internationalShippingCheckbox.change(function () {
        if (internationalShippingCheckbox.is(':checked')) {
            $('#international_shipping_form').show();
        } else {
            $('#international_shipping_form').hide();
        }
    })
});