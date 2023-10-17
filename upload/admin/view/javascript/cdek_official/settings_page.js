$(document).ready(function() {
    // let internationalShippingCheckbox = $('#cdek_official_seller_international_shipping_checkbox');
    // internationalShippingCheckbox.change(function () {
    //     if (internationalShippingCheckbox.is(':checked')) {
    //         $('#international_shipping_form').show();
    //     } else {
    //         $('#international_shipping_form').hide();
    //     }
    // })

    let timer;

    $('#cdek_official_shipping__city').on('input', function() {
        if ($(this).val() === '') {
            $('#city-choices').empty();
            $('#city-choices').addClass('hidden');
            return;
        }

        $('#preloader').removeClass('hidden');
        clearTimeout(timer);
        timer = setTimeout(() => {
            $.ajax({
                url: 'index.php?route=extension/shipping/cdek_official&user_token=' + userToken,
                type: 'post',
                data: {
                    cdekRequest: 'getCity',
                    key: $(this).val()
                },
                dataType: 'json',
                success: function(response) {
                    console.log("Success: ", response);
                    $('#city-choices').empty();
                    $.each(response, function(index, item) {
                        $('#city-choices').append('<div class="city-choice" data-code="' + item.code + '">' + item.country + ', ' + item.region + ', ' + item.city + '</div>');
                    });

                    $('.city-choice').click(function() {
                        let selectedCity = $(this).text();
                        let selectedCode = $(this).data('code');

                        $('#city-choices').addClass('hidden');
                        $('#cdek_official_shipping__city').val(selectedCity);
                        $('#cdek_official_shipping__city_code').val(selectedCode);
                        $('#city-choices').empty();
                    });

                    $('#preloader').addClass('hidden');

                    if ($('#city-choices').is(':empty')) {
                        $('#city-choices').addClass('hidden');
                    } else {
                        $('#city-choices').removeClass('hidden');
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    console.log("Error: " + thrownError);
                    console.log("Status: " + xhr.status);
                    console.log("Response: " + xhr.responseText);

                    $('#preloader').addClass('hidden');
                }
            });
        }, 1000);
    });


    $('#clear-button-pvz').on('click', function() {
        $('#cdek_official_shipping__pvz').val('');
    })

    $('#clear-button-address').on('click', function() {
        $('#cdek_official_shipping__city_address').val('');
    })
});