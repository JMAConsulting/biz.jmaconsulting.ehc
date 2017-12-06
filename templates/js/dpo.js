CRM.$(function($) {

  $('div.other_amount-section').find('label').css('display', 'none');

  showHideOtherAmount($('.contribution_amount-section input[type="radio"]:checked').val());
  $('.contribution_amount-section input').click(function(){
    showHideOtherAmount($(this).val());
  });
  function showHideOtherAmount(value) {
    if (value == 0) {
     $('div.other_amount-section').show();
    }
    else {
      $('div.other_amount-section').hide();
    }
  }
});
