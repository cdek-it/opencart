<?php
class ControllerExtensionShippingCdekOfficial extends Controller {

    public function cdek_official_checkout_shipping_after(&$route, &$data, &$output)
    {
        $cdekBlock = '<p><strong>CDEK Official Shipping</strong></p>';
        $errorBlock = '<div id="cdek_number_customer_error" style="color: red; display: none">Введите телефон</div>';
        $phoneBlock = '<div class="row required">
      <label class="col-sm-1 control-label" for="input-shipping-firstname">Phone</label>
      <div class="col-sm-3">
        <input type="tel" name="cdek_number_customer" value="" placeholder="Phone" id="cdek_number_customer" class="form-control">
      </div>
      <div class="col-sm-8"></div>
    </div>';

        $btnShippingContinue = "$(document).delegate('#button-shipping-method', 'click', function() {";
        $validatePhone = "if ($('#cdek_number_customer').val() === '') {
        $('#cdek_number_customer_error').show()
        return;
    }
    $('#cdek_number_customer_error').hide()";

        $this->searchAndReplace($output, $cdekBlock, $phoneBlock);
        $this->searchAndReplace($output, $cdekBlock, $errorBlock);
        $this->searchAndReplace($output, $btnShippingContinue, $validatePhone);

    }

    private function searchAndReplace(&$output, $search, $replace)
    {
        $pos = strpos($output, $search);

        if ($pos !== false) {
            $insertPos = $pos + strlen($search);
            $output = substr_replace($output, $replace, $insertPos, 0);
        }

    }
}