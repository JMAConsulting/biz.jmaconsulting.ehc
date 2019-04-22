<div id="split_payment_section"><div class="label">{$form.split_payment.label}</div> <div class="content">{$form.split_payment.html}</div><div class="clear"></div></div>
{literal}
<script type="text/javascript">
CRM.$(function($) {
  $('div#split_payment_section').insertBefore($('.i_would_like_to_split_my_sponsorship_payment_by_paying_this_amount_now_and_the_remainder_later_-section').prev().prev());

  if ($('#split_payment').is(":checked")) {
    $('.i_would_like_to_split_my_sponsorship_payment_by_paying_this_amount_now_and_the_remainder_later_-section').show();
    $('.i_would_like_to_split_my_sponsorship_payment_by_paying_this_amount_now_and_the_remainder_later_-section').prev().show();
    $('.i_would_like_to_split_my_sponsorship_payment_by_paying_this_amount_now_and_the_remainder_later_-section').prev().prev().show();
    $('.split_payment_for_sponsorships-section').show();
    $('.split_payment_for_sponsorships-section').prev().show();
    $('.split_payment_for_sponsorships-section').prev().prev().show();
  }
  else {
    $('.i_would_like_to_split_my_sponsorship_payment_by_paying_this_amount_now_and_the_remainder_later_-section').hide();
    $('.i_would_like_to_split_my_sponsorship_payment_by_paying_this_amount_now_and_the_remainder_later_-section').prev().hide();
    $('.i_would_like_to_split_my_sponsorship_payment_by_paying_this_amount_now_and_the_remainder_later_-section').prev().prev().hide();
    $('.split_payment_for_sponsorships-section').hide();
    $('.split_payment_for_sponsorships-section').prev().hide();
    $('.split_payment_for_sponsorships-section').prev().prev().hide();
  }


  $('#split_payment').change(function() {
    if ($(this).is(":checked")) {
      $('.i_would_like_to_split_my_sponsorship_payment_by_paying_this_amount_now_and_the_remainder_later_-section').show();
      $('.i_would_like_to_split_my_sponsorship_payment_by_paying_this_amount_now_and_the_remainder_later_-section').prev().show();
      $('.i_would_like_to_split_my_sponsorship_payment_by_paying_this_amount_now_and_the_remainder_later_-section').prev().prev().show();
      $('.split_payment_for_sponsorships-section').show();
      $('.split_payment_for_sponsorships-section').prev().show();
      $('.split_payment_for_sponsorships-section').prev().prev().show();
    }
    else {
      $('.i_would_like_to_split_my_sponsorship_payment_by_paying_this_amount_now_and_the_remainder_later_-section').hide();
      $('.i_would_like_to_split_my_sponsorship_payment_by_paying_this_amount_now_and_the_remainder_later_-section').prev().hide();
      $('.i_would_like_to_split_my_sponsorship_payment_by_paying_this_amount_now_and_the_remainder_later_-section').prev().prev().hide();
      $('.split_payment_for_sponsorships-section').hide();
      $('.split_payment_for_sponsorships-section').prev().hide();
      $('.split_payment_for_sponsorships-section').prev().prev().hide();
      $('#price_52').val('');
      $('#price_52').trigger('keyup');
      $("#s2id_price_53").select2("val", "");
    }
  });
});
</script>
{/literal}
