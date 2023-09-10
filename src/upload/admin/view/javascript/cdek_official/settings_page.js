$(document).ready(function() {
    let internationalShippingCheckbox = $('#cdek_official_seller_international_shipping_checkbox');
    internationalShippingCheckbox.change(function () {
        if (internationalShippingCheckbox.is(':checked')) {
            $('#international_shipping_form').show();
        } else {
            $('#international_shipping_form').hide();
        }
    })

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

    $('#cdek_official_shipping__pvz').on('input', function() {
        console.log($(this).val())
        console.log(userToken);
        $('#preloader-pvz').removeClass('hidden');
        clearTimeout(timer);
        timer = setTimeout(() => {
            $.ajax({
                url: 'index.php?route=extension/shipping/cdek_official&user_token=' + userToken,
                type: 'post',
                data: {
                    cdekRequest: 'getPvz',
                    key: $('#cdek_official_shipping__city_code').val(),
                    street: $(this).val()
                },
                dataType: 'json',
                success: function(response) {
                    console.log("Success: ", response);
                    $('#pvz-choices').empty();
                    $.each(response, function(index, item) {
                        $('#pvz-choices').append('<div class="pvz-choice" data-code="' + item.code + '">' + item.address + '</div>');
                    });

                    $('.pvz-choice').click(function() {
                        let selectedPvz = $(this).text();
                        let selectedCode = $(this).data('code');

                        $('#pvz-choices').addClass('hidden');
                        $('#cdek_official_shipping__pvz').val(selectedPvz);
                        $('#cdek_official_shipping__pvz_code').val(selectedCode);
                        $('#pvz-choices').empty();
                    });

                    $('#preloader-pvz').addClass('hidden');

                    if ($('#pvz-choices').is(':empty')) {
                        $('#pvz-choices').addClass('hidden');
                    } else {
                        $('#pvz-choices').removeClass('hidden');
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    console.log("Error: " + thrownError);
                    console.log("Status: " + xhr.status);
                    console.log("Response: " + xhr.responseText);

                    $('#preloader-pvz').addClass('hidden');
                }
            });
        }, 1000);
    })
});