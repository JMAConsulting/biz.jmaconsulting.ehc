{literal}
    <script type="text/javascript">
        CRM.$(function ($) {
            $('#payment_instrument_id').change(showHidePI);
            $('#financial_type_id').on('change', function(e) {
              if ($('div.check_number-section').next('tr.custom_279_-1-row').length) {
                $('div.check_number-section').next('tr.custom_279_-1-row').remove();
                $('tr.custom_279_-1-row').insertAfter('div.check_number-section');
              }
            });
            $( document ).ajaxComplete(function() {
                showHidePI();
            });
            function showHidePI() {
                var $paymentInstrumentID = $('#payment_instrument_id').val();
                $('tr.custom_279_-1-row').css('display', 'block');
                if ($paymentInstrumentID == 4) {
                  if (!$('div.check_number-section').next('tr.custom_279_-1-row').length) {
                    $('tr.custom_279_-1-row').show();
                    $('tr.custom_279_-1-row').insertAfter('div.check_number-section');
                  }
                  else {
                    $('div.check_number-section').next('tr.custom_279_-1-row').remove();
                    $('tr.custom_279_-1-row').insertAfter('div.check_number-section');
                  }
                }
                else {
                    $('tr.custom_279_-1-row').hide();
                    $('tr.custom_279_-1-row').insertAfter('tr.custom_269_-1-row');
                }
            }
        });
    </script>
{/literal}
