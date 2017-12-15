<?php
Class CRM_SALTA_Import {

  public $civicrmPath = '';
  public $sourceContactId = '';

  function __construct() {
    // you can run this program either from an apache command, or from the cli
    $this->initialize();
  }

  function initialize() {
    $civicrmPath = $this->civicrmPath;
    require_once $civicrmPath .'civicrm.config.php';
    require_once $civicrmPath .'CRM/Core/Config.php';
    $config = CRM_Core_Config::singleton();
  }

  function createActivitiesFromTags() {
    $mappingsOptionGroupName = 'SALTA_Tag-Activity_import_mappings';
    $mappingsOptionGroupValues = CRM_Core_DAO::executeQuery("
      SELECT cv.label, cv.value, cv.description
      FROM civicrm_option_value cv
        INNER JOIN civicrm_option_group cg ON cv.option_group_id = cg.id
          AND cg.name = '{$mappingsOptionGroupName}'
      "
    );
    // create activity type SALTA
    $activityTypeName = 'SALTA Tags';
    try {
      $result = civicrm_api3('OptionValue', 'getvalue', [
        'return' => "value",
        'option_group_id' => "activity_type",
        'name' => "{$activityTypeName}",
      ]);
    }
    catch (CiviCRM_API3_Exception $e) {
      //create Activity type Salta Tag
      civicrm_api3('OptionValue', 'create', [
        'option_group_id' => "activity_type",
        'label' => "{$activityTypeName}",
        'name' => "{$activityTypeName}",
      ]);
    }
    $sourceContactId = $this->sourceContactId;
    while ($mappingsOptionGroupValues->fetch()) {
      // get Contacts from tag name
      $result = civicrm_api3('EntityTag', 'get', [
        'return' => ["entity_id", "tag_id"],
        'entity_table' => "civicrm_contact",
        'tag_id.name' => $mappingsOptionGroupValues->label,
        'options' => ['limit' => 0],
      ]);
      $activityDate = $this->buildDate($mappingsOptionGroupValues->description);
      foreach ($result['values'] as $value) {
        try {
          civicrm_api3('Activity', 'getsingle', [
            'activity_type_id' => $activityTypeName,
            'assignee_contact_id' => $value['entity_id'],
            'options' => ['limit' => 1],
            'source_record_id' => $value['tag_id'],
          ]);
        }
        catch (CiviCRM_API3_Exception $e) {
          $activityParams = [
            'activity_type_id' => $activityTypeName,
            'subject' => $mappingsOptionGroupValues->value,
            'source_record_id' => $value['tag_id'],
            'activity_date_time' => $activityDate,
            'status_id' => 'Completed',
            'created_date' => $activityDate,
            'assignee_contact_id' => [$value['entity_id']],
            'source_contact_id' => $sourceContactId,
          ];
          $activity = civicrm_api3('Activity', 'create', $activityParams);
          // Print on Screen the params and error
          if (empty($activity['id'])) {
            CRM_Core_Error::debug('activity', $activity);
            CRM_Core_Error::debug('activityParams', $activityParams);
          }
          else {
            civicrm_api3('EntityTag', 'create', [
              'entity_table' => "civicrm_activity",
              'tag_id' => $value['tag_id'],
              'entity_id' => $activity['id'],
            ]);
          }
        }
      }
    }
  }

  /*
  * Build Date using string.
  */
  protected function buildDate($dateString) {
    $dateString = strip_tags($dateString);
    $dateString = trim($dateString);
    if (strlen($dateString) == 4) {
      $dateString = '01-01-' . $dateString;
    }
    $date = date('Ymd', strtotime($dateString));
    return $date;
  }

}

$import = new CRM_SALTA_Import();
$import->createActivitiesFromTags();
