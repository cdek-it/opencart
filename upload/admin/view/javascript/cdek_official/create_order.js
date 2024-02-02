$(() => {
    const init = () => {
        $('#create-order-form').submit(e => {
            e.preventDefault();
            e.stopPropagation();
            $('#cdek_order_create_success').hide();
            $('#cdek_order_create_error').hide();
            $('#cdek-loader').show();
            $('#cdek_official_order_info').parent()
                                          .load(e.target.action, {
                                              length: $('#cdek_order_length')
                                                .val(),
                                              width: $('#cdek_order_width')
                                                .val(),
                                              height: $('#cdek_order_height')
                                                .val(),
                                          }, init);
        });

        $('#cdek_delete_order_btn').click(function(event) {
            event.preventDefault();
            $.ajax({
                       url: 'index.php?route=extension/shipping/cdek_official&user_token=' +
                         userToken, type: 'POST', data: {
                    cdekRequest: 'deleteOrder',
                    uuid: $(event.currentTarget).data('uuid'),
                    order_id: orderId,
                }, success: function(response) {
                    let resp = JSON.parse(response);
                    if (!resp.state) {
                        $('#cdek_order_create_error').text(resp.message).show();
                        return;
                    }
                    $('#cdek_order_deleted #cdek_order_number_name')
                      .html(resp.order.cdek_number);
                    $('#cdek_order_deleted #cdek_order_customer_name')
                      .html(resp.order.name);
                    $('#cdek_order_deleted #cdek_order_type_name')
                      .html(resp.order.type);
                    $('#cdek_order_deleted #cdek_order_payment_type_name')
                      .html(resp.order.payment_type);
                    $('#cdek_order_deleted #cdek_order_direction_name')
                      .html(resp.order.to_location);
                    $('#cdek_order_deleted #cdek_order_pvz_code')
                      .html(resp.order.pvz_code);
                    $('#cdek_order_created').hide();
                    $('#cdek_order_deleted').show();
                }, error: function(error) {
                    console.log(error);
                },
                   });
        });

        $('#cdek_get_bill_btn').click(function(event) {
            event.preventDefault();

            var link = document.createElement('a');
            link.target = '_blank';
            link.href =
              'index.php?route=extension/shipping/cdek_official&user_token=' +
              userToken + '&cdekRequest=getBill&&uuid=' +
              $(event.currentTarget).data('uuid');
            link.click();
        });

        $('#cdek_recreate_btn').click(function(event) {
            event.preventDefault();
            $('#cdek_order_create_form').show();
            $('#cdek_order_created').hide();
            $('#cdek_order_deleted').hide();
        });
    };

    init();
});
