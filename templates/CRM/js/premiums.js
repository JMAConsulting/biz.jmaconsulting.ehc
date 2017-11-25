CRM.$(function($) {
  showHideOtherAmount($('.contribution_amount-section input[type="radio"]:checked'));
  $('.contribution_amount-section input').click(function(){
    showHideOtherAmount($(this));
  });

  function showHideOtherAmount(obj) {
    var value = $(obj).val();
    if (typeof value === 'undefined') {
      return false;
    }
    var dataValue = JSON.parse($(obj).attr('data-price-field-values'));
    dataValue = dataValue[value];
    var priceName = dataValue.name;

    if (priceName.indexOf('_two_') !== -1) {
      showCustomFields();
    }
    else {
      hideCustomFields();
    }
  }
  function showCustomFields() {
    $('#editrow-custom_59, #editrow-custom_57, #editrow-custom_61, #editrow-custom_63').show();
  }
  function hideCustomFields() {
    $('#editrow-custom_59, #editrow-custom_57, #editrow-custom_61, #editrow-custom_63').hide();
    $('#custom_59, #custom_61').val('');
    $('#custom_57,  #custom_63').select2('val', '');
  }
  //hide premiums
  $('.premiums_select-group #premiums-listings').hide();

  //code to hide on Confirm and ThankYou Pages
  var options = CRM.showHideOption;
  if (options == 'show') {
    showCustomFields();
  }
  else if (options == 'hide') {
    hideCustomFields();
  }
});
