$(() => {
    const init = () => {
        $('#create-order-form').submit(e => {
            e.preventDefault();
            e.stopPropagation();
            $('#cdek_order_create_error').hide();
            $('.cdek-loader').show();
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

        $('#cdek_delete_order_btn').on('click', e => {
            e.preventDefault();
            e.stopPropagation();
            $('.cdek-loader').show();
            $('#cdek_official_order_info').parent().load(e.target.href,
                                                         {},
                                                         init);
        });

        $('#cdek_recreate_btn').on('click', e => {
            e.preventDefault();
            e.stopPropagation();
            $('#cdek_order_create_form').show();
            $('#cdek_order_created').hide();
            $('#cdek_order_deleted').hide();
        });
    };

    init();
});
