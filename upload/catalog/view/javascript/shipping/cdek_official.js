'use strict';

$(() => {

    function debounce(func, ms) {
        let timeout;
        return function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, arguments), ms);
        };
    }

    const chooseOfficeText = 'Выбрать пункт выдачи';
    const changeOfficeText = 'Изменить пункт выдачи';
    const loadErrorText = 'Не удалось загрузить пункты выдачи. Попробуйте ещё раз.';

    let mapScriptPromise = null;

    const loadMapScript = () => {
        if (Object.prototype.hasOwnProperty.call(window, 'CDEKWidget')) {
            return $.Deferred().resolve().promise();
        }

        if (mapScriptPromise !== null) {
            return mapScriptPromise;
        }

        const deferred = $.Deferred();
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/@cdek-it/widget@3.10.4/dist/cdek-widget.umd.js';
        script.integrity = 'sha384-d3BX/k7LeLv5Ld8LGJ/XhQKQarz+3sxJrRzNRfjTjuSrLNFvz3LFYD4aV610Jg6s';
        script.crossOrigin = 'anonymous';
        script.onload = () => deferred.resolve();
        script.onerror = () => {
            script.remove();
            mapScriptPromise = null;
            deferred.reject(new Error('CDEK widget failed integrity or network validation'));
        };
        document.head.appendChild(script);
        mapScriptPromise = deferred.promise();

        return mapScriptPromise;
    };

    const updateGlobalData = () => $.getJSON(
      'index.php?route=extension/shipping/cdek_official/getParams',
      {})
      .then(data => {
          window.cdek = data;

          return loadMapScript().then(() => data);
      });

    const addButton = debounce((e) => {
        const method = e.target.value;
        const existing = $('.cdek_btn');
        if (method.indexOf('cdek_official.') !== 0 || method.split('.')[1].indexOf('office') === -1) {
            existing.closest('[data-shipping-method-form]').prop('hidden', false);
            existing.remove();
            $('.cdek_office_info, .cdek_picker_error, #cdek_official_office_error').remove();
            return;
        }

        const object = $(e.target);
        const card = object.closest('.shipping-method-card');
        const label = object.closest('label');
        const anchor = card.length ? card : (label.length ? label : object);
        const methodForm = card.closest('[data-shipping-method-form]');

        if (!existing.length || existing.attr('data-shipping-code') !== method || !anchor.nextAll('.cdek_btn').length) {
            existing.remove();
            $('.cdek_office_info, .cdek_picker_error, #cdek_official_office_error').remove();
            const button = $('<button class="cdek_btn btn btn-default" type="button"></button>')
              .text(chooseOfficeText)
              .attr('data-shipping-code', method);
            const status = $('<div id="cdek_official_office_error" class="alert alert-danger" role="alert"></div>')
              .text(loadErrorText);
            anchor.after(button);

            let errorView;
            let retry;
            if (methodForm.length) {
                const title = $('<h3 id="cdek-picker-error-title" tabindex="-1">Выбор пункта выдачи</h3>');
                retry = $('<button type="button" class="btn btn-default">Повторить</button>');
                const back = $('<button type="button" class="btn btn-default">Вернуться к доставке</button>');
                errorView = $('<section class="cdek_picker_error" aria-labelledby="cdek-picker-error-title" hidden></section>')
                  .append(title, status, retry, back)
                  .insertAfter(methodForm);
                back.on('click', () => {
                    errorView.prop('hidden', true);
                    methodForm.prop('hidden', false);
                    button[0].focus();
                });
            } else {
                status.hide().insertAfter(button);
            }

            const showError = () => {
                if (errorView) {
                    methodForm.prop('hidden', true);
                    errorView.prop('hidden', false);
                    errorView.find('h3')[0].focus();
                } else {
                    status.show();
                    button.text('Повторить');
                }
            };

            const openPicker = () => {
                const trigger = retry && !errorView.prop('hidden') ? retry : button;
                trigger.prop('disabled', true);
                if (!errorView) status.hide();

                const stillChoosing = () => button[0].isConnected && object.is(':checked') &&
                  (trigger === button || !errorView.prop('hidden'));

                updateGlobalData().then(() => {
                    if (!stillChoosing()) return;

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
                            servicePath: 'index.php?route=extension/shipping/cdek_official/map',
                            onChoose: (type, tariff, address) => {
                                $.post(
                                  'index.php?route=extension/shipping/cdek_official/cacheOfficeCode',
                                  {office_code: address.code, office_address: address.address})
                                  .done(updateGlobalData);
                            },
                        });
                    } else {
                        window.cdekWidget.updateLocation(window.cdek.city);
                        return $.get('index.php?route=extension/shipping/cdek_official/map')
                          .then(data => window.cdekWidget.updateOfficesRaw(data));
                    }
                }).then(() => {
                    if (!stillChoosing()) return;

                    if (errorView && !errorView.prop('hidden')) {
                        errorView.prop('hidden', true);
                        methodForm.prop('hidden', false);
                    }
                    button.text(window.cdek.office_code ? changeOfficeText : chooseOfficeText);
                    window.cdekWidget.open();
                }).fail(() => {
                    if (stillChoosing()) showError();
                }).always(() => trigger.prop('disabled', false));
            };

            button.on('click', openPicker);
            if (retry) retry.on('click', openPicker);
        }

        if (!Object.prototype.hasOwnProperty.call(window, 'cdek') ||
          !Object.prototype.hasOwnProperty.call(window.cdek, 'office_code') ||
          window.cdek.office_code === null) {
            return;
        }
        $('.cdek_office_info').remove();
        const button = $('button.cdek_btn');
        if (button.text() !== 'Повторить') button.text(changeOfficeText);
        const office = $('<span class="cdek_office_info"></span>')
          .text(`[${window.cdek.office_code}] ${window.cdek.office_address}`);
        if (card.length) {
            card.append(office);
        } else {
            office.insertBefore(button);
        }
    }, 100);

    const checkShippingMethods = debounce(() => {
        const shippingInputs = $('[name="shipping_method"]');
        if (shippingInputs.length === 0) return;

        shippingInputs.off('change.cdek').on('change.cdek', addButton);

        shippingInputs.each((i, e) => {
            if (e.type === 'radio' && !e.checked) return;
            addButton({ target: e });
        });
    }, 300);

    $(document).on('ajaxComplete', checkShippingMethods);
    checkShippingMethods();
});
