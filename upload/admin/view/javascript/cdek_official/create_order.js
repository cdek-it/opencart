$(document).ready(function() {
    $('#create-order').click(function (event) {
        event.preventDefault();
        $('#cdek_order_create_success').hide();
        $('#cdek_order_create_error').hide();
        let data = {
            length: $('#cdek_order_length').val(),
            width: $('#cdek_order_width').val(),
            height: $('#cdek_order_height').val()
        };
        $.ajax({
            url: 'index.php?route=extension/shipping/cdek_official&user_token=' + userToken,
            type: 'POST',
            data: {
                cdekRequest: 'createOrder',
                dimensions: data,
                order_id: orderId
            },
            beforeSend: function() {
                $('#cdek-loader').show();
            },
            complete: function() {
                $('#cdek-loader').hide();
            },
            success: function(response) {
                let resp = JSON.parse(response);
                if (resp.state === false) {
                    $('#cdek_order_create_error').text(resp.message).show()
                    return;
                }
                $('#cdek_order_number_name').text(resp.data.cdek_number);
                $('#cdek_order_customer_name').text(resp.data.name);
                $('#cdek_order_type_name').text(resp.data.type);
                $('#cdek_order_payment_type_name').text(resp.data.payment_type);
                $('#cdek_order_direction_name').text(resp.data.to_location);
                if (resp.data.pvz_code !== "") {
                    $('#cdek_order_pvz_code').text(resp.data.pvz_code);
                    $('#cdek_order_pvz_code_tr').show();
                }
                $('#cdek_delete_order_btn').attr('data-uuid', resp.data.cdek_uuid);
                $('#cdek_get_bill_btn').attr('data-uuid', resp.data.cdek_uuid);
                $('#cdek_order_create_form').hide();
                $('#cdek_order_created').show();
            },
            error: function(error) {
                console.log(error);
            }
        });
    });

    $('#cdek_delete_order_btn').click(function (event) {
        event.preventDefault();
        $.ajax({
            url: 'index.php?route=extension/shipping/cdek_official&user_token=' + userToken,
            type: 'POST',
            data: {
                cdekRequest: 'deleteOrder',
                uuid: $(event.currentTarget).data('uuid'),
                order_id: orderId
            },
            success: function(response) {
                let resp = JSON.parse(response);
                if (!resp.state) {
                    $('#cdek_order_create_error').text(resp.message).show()
                    return;
                }
                $('#cdek_order_deleted #cdek_order_number_name').html(resp.order.cdek_number)
                $('#cdek_order_deleted #cdek_order_customer_name').html(resp.order.name)
                $('#cdek_order_deleted #cdek_order_type_name').html(resp.order.type)
                $('#cdek_order_deleted #cdek_order_payment_type_name').html(resp.order.payment_type)
                $('#cdek_order_deleted #cdek_order_direction_name').html(resp.order.to_location)
                $('#cdek_order_deleted #cdek_order_pvz_code').html(resp.order.pvz_code)
                $('#cdek_order_created').hide()
                $('#cdek_order_deleted').show()
            },
            error: function(error) {
                console.log(error);
            }
        });
    })

    $('#cdek_get_bill_btn').click(function (event) {
        event.preventDefault();

        var link = document.createElement('a');
        link.target = '_blank';
        link.href = 'index.php?route=extension/shipping/cdek_official&user_token=' + userToken +'&cdekRequest=getBill&&uuid=' + $(event.currentTarget).data('uuid');
        link.click();
    })

    $('#cdek_recreate_btn').click(function (event) {
        event.preventDefault();
        $('#cdek_order_create_form').show();
        $('#cdek_order_created').hide();
        $('#cdek_order_deleted').hide();
    })
});