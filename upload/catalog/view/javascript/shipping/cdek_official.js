'use strict';

$(() => {

    function debounce(func, ms) {
        let timeout;
        return function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, arguments), ms);
        };
    }

    const updateGlobalData = () => $.getJSON(
      'index.php?route=extension/shipping/cdek_official/getParams',
      (data) => {
          window.cdek = data;
      });

    const checkShippingMethods = debounce(() => {
        const shippingInputs = $('input[name="shipping_method"]');
        if (shippingInputs.length === 0) return;

        $.when(shippingInputs.each((i, e) => {
            if (e.value.indexOf('cdek_official') === -1) return;

            const shippingMethod = e.value.split('.')[1];

            if (shippingMethod.indexOf('office') === -1) return;

            const object = $(e);
            if (object.nextAll('.cdek_btn').length) return;

            object.after($('<button class="cdek_btn">Выбрать ПВЗ</button>').on(
              'click',
              () => {
                  updateGlobalData().done(() => {
                      if (window.cdekWidget === undefined) {
                          window.cdekWidget = new window.CDEKWidget({
                                                                        apiKey: window.cdek.apikey,
                                                                        defaultLocation: window.cdek.city,
                                                                        popup: true,
                                                                        canChoose: true,
                                                                        hideDeliveryOptions: {
                                                                            office: false,
                                                                            door: true,
                                                                        },
                                                                        servicePath: '/index.php?route=extension/shipping/cdek_official/map',
                                                                        onChoose: function(type,
                                                                          tariff,
                                                                          address) {
                                                                            $.post(
                                                                              '/index.php?route=extension/shipping/cdek_official/cacheOfficeCode',
                                                                              {
                                                                                  office_code: address.code,
                                                                                  office_address: address.address,
                                                                              });
                                                                            $('.cdek_office_info')
                                                                              .remove();
                                                                            window.cdek.office_code = address.code;
                                                                            window.cdek.office_address = address.address;
                                                                            $('button.cdek_btn')
                                                                              .before(
                                                                                $('<div class="cdek_office_info"></div>')
                                                                                  .html(
                                                                                    `[${address.code}] ${address.address}`));
                                                                        },
                                                                    });
                      } else {
                          window.cdekWidget.updateLocation(window.cdek.city);
                          $.get(
                            '/index.php?route=extension/shipping/cdek_official/map')
                           .done(data => {
                               $('#cdek_official_office_error').hide();
                               window.cdekWidget.updateOfficesRaw(data);
                           }).fail(xhr => {
                              const error = JSON.parse(xhr.responseText);
                              console.debug('[CDEKDelivery] ' + error.message);
                              $('#cdek_official_office_error')
                                .text(error.message)
                                .show();
                              window.cdekWidget.close();
                          });
                      }
                      window.cdekWidget.open();
                  });
              }));
        })).done(() => {
            if (!Object.prototype.hasOwnProperty.call(window, 'cdek') ||
              !Object.prototype.hasOwnProperty.call(window.cdek,
                                                    'office_code') ||
              window.cdek.office_code === null) {
                return;
            }
            $('.cdek_office_info').remove();
            $('button.cdek_btn')
              .before($('<div class="cdek_office_info"></div>')
                        .html(`[${window.cdek.office_code}] ${window.cdek.office_address}`));
        });
    }, 300);

    $(document).on('ajaxComplete', checkShippingMethods);
    checkShippingMethods();
});
