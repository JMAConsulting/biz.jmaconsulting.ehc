{if $isView}
{literal}
<script type="text/javascript">
CRM.$(function($) {
  var subact = $("[id^=Sub_Activity_]").find('table.crm-info-panel').find('tr');
  subact.insertAfter($('.crm-activity-form-block-subject'));
  $("[id^=Sub_Activity_]").hide();
});
</script>
{/literal}
{else}
{literal}
<script type="text/javascript">
CRM.$(function($) {
  addSubActivity($('#activity_type_id').val());
  $('#activity_type_id').change(function() {
    addSubActivity($(this).val());
  });

  function addSubActivity(aid) {
    if (aid == 66) {
      $( document ).ajaxComplete(function( event, xhr, settings ) {
        var url = settings.url;
        if (url.indexOf('civicrm/custom') != -1) {
          $('.custom_276_-1-row').insertAfter('.crm-activity-form-block-activity_type_id');
          $('.custom_276_1-row').insertAfter('.crm-activity-form-block-activity_type_id');
          $('.custom-group-Sub_Activity').hide();
        }
      });
    }
    else {
      $('#custom_276_-1').select2('val', '');
      $('.custom_276_-1-row').hide();
    }
  }
  
});
</script>
{/literal}
{/if}
