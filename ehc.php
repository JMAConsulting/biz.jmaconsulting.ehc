<?php

require_once 'ehc.civix.php';
require_once 'ehc.variables.php';
/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function ehc_civicrm_config(&$config) {
  _ehc_civix_civicrm_config($config);
  CRM_Core_Resources::singleton()->addStyleFile('biz.jmaconsulting.ehc', 'css/style.css');
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function ehc_civicrm_xmlMenu(&$files) {
  _ehc_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function ehc_civicrm_install() {
  civicrm_api3('OptionValue', 'create', array(
    'option_group_id' => 'cg_extend_objects',
    'label' => ts('Contribution Page'),
    'name' => 'civicrm_contribution_page',
    'value' => 'ContributionPage',
    'is_active' => 1,
  ));
  _ehc_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function ehc_civicrm_postInstall() {
  _ehc_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function ehc_civicrm_uninstall() {
  _ehc_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function ehc_civicrm_enable() {
  _ehc_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function ehc_civicrm_disable() {
  _ehc_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function ehc_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _ehc_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function ehc_civicrm_managed(&$entities) {
  _ehc_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function ehc_civicrm_caseTypes(&$caseTypes) {
  _ehc_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function ehc_civicrm_angularModules(&$angularModules) {
  _ehc_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function ehc_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _ehc_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

function ehc_civicrm_preProcess($formName, &$form) {
  if ($formName == 'CRM_Contribute_Form_ContributionPage_Settings') {
    $form->assign('customDataType', 'ContributionPage');
    $id = $form->getVar('_id');
    if ($id) {
      $form->assign('entityID', $id);
    }
    if (!empty($_POST['hidden_custom'])) {
      $form->set('type', 'ContributionPage');
      CRM_Custom_Form_CustomData::preProcess($form, NULL, NULL, 1, 'ContributionPage', $id);
      CRM_Custom_Form_CustomData::buildQuickForm($form);
      CRM_Custom_Form_CustomData::setDefaultValues($form);
    }
  }
}

function getCustomColumnsByEntity($entity, $getID = FALSE) {
  $customColumns = array();
  $dao = CRM_Core_DAO::executeQuery(
    "SELECT table_name, column_name, cf.id as cfid FROM civicrm_custom_group cg
   INNER JOIN civicrm_custom_field cf ON cf.custom_group_id = cg.id
   WHERE cg.extends = '{$entity}' AND (LOWER(cf.name) LIKE '%solicit_code%' OR LOWER(cf.name) LIKE '%sub_solicit%')
   "
  );
  while ($dao->fetch()) {
    $type = strstr($dao->column_name, 'sub_solicit') ? 'ssc' : 'sc';
    if ($getID) {
      $customColumns[$entity][$type] = $dao->cfid;
    }
    else {
      $customColumns[$dao->table_name][$type] = $dao->column_name;
    }
  }
  return $customColumns;
}

function ehc_civicrm_pre($op, $objectName, $id, &$params) {
  if ($op == 'create' && empty($id) && $objectName == 'Contribution') {
    if (!empty($params['custom_256_-1']) ) {
      $sc = CRM_Core_DAO::singleValueQuery("SELECT name FROM civicrm_option_value WHERE value = '" . $params['custom_256_-1'] . "' AND option_group_id = 96");
      CRM_Core_Smarty::singleton()->assign('sc', $sc);
    }
    else {
      CRM_Core_Smarty::singleton()->assign('sc', NULL);
    }
    if (!empty($params['custom_257_-1'])) {
      $ssc = CRM_Core_DAO::singleValueQuery("SELECT name FROM civicrm_option_value WHERE value = '" . $params['custom_257_-1'] . "' AND option_group_id = 97");
      CRM_Core_Smarty::singleton()->assign('ssc', $ssc);
    }
    else {
      CRM_Core_Smarty::singleton()->assign('ssc', NULL);
    }
  }
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function ehc_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  if ($op == 'create') {
    if ($objectName == 'ParticipantPayment') {
      $customColumns = CRM_Core_BAO_Cache::getItem('ehc custom columns', 'event columns');
      $contriCustomIDs = CRM_Core_BAO_Cache::getItem('ehc custom columns', 'contribution columns');
      if (!$customColumns) {
        $customColumns = getCustomColumnsByEntity('Event');
        CRM_Core_BAO_Cache::setItem($customColumns, 'ehc custom columns', 'event columns');
      }
      if (!$contriCustomIDs) {
        $contriCustomIDs = getCustomColumnsByEntity('Contribution', TRUE);
        CRM_Core_BAO_Cache::setItem($contriCustomIDs, 'ehc custom columns', 'contribution columns');
      }
      if (!empty($customColumns) && !empty($contriCustomIDs)) {
        $eventID = CRM_Core_DAO::getFieldValue('CRM_Event_DAO_Participant', $objectRef->participant_id, 'event_id');
        $tablename = key($customColumns);
        $result = CRM_Core_DAO::executeQuery(sprintf(" SELECT %s FROM %s WHERE entity_id = %d ",implode(',', $customColumns[$tablename]), $tablename, $eventID))->fetchAll();
        $params = array('id' => $objectRef->contribution_id);
        foreach ($result as $value) {
          foreach ($customColumns as $tableName => $keys) {
            foreach (array('sc', 'ssc') as $type) {
              if (!empty($contriCustomIDs['Contribution'][$type]) && !empty($value[$keys[$type]])) {
                $params['custom_' . $contriCustomIDs['Contribution'][$type]] = $value[$keys[$type]];
              }
            }
          }
        }
        civicrm_api3('Contribution', 'create', $params);
      }
    }
    if ($objectName == "Contribution") {
      $contribution = civicrm_api3('Contribution', 'getsingle', array(
        'return' => array("financial_type_id", "non_deductible_amount", "custom_256"),
        'id' => $objectId,
      ));
      $org = civicrm_api3('EntityFinancialAccount', 'getsingle', array(
        'return' => array("financial_account_id.contact_id.legal_name", "financial_account_id.contact_id.organization_name"),
        'entity_table' => "civicrm_financial_type",
        'entity_id' => $contribution["financial_type_id"],
        'options' => array('limit' => 1),
      ));
      // Insert the recurring custom field value.
      if (!empty($objectRef->contribution_recur_id)) {
        civicrm_api3('Contribution', 'create', array('id' => $objectRef->id, 'custom_268' => 1, 'custom_269' => 'Recurring'));
      }
      else {
        $ftName = CRM_Contribute_PseudoConstant::financialType($objectRef->financial_type_id);
        $sc = CRM_Core_Smarty::singleton()->get_template_vars('sc');
        $ssc = CRM_Core_Smarty::singleton()->get_template_vars('ssc');
        civicrm_api3('Contribution', 'create', array('id' => $objectRef->id, 'custom_269' => getFinanceColumn($ftName, $sc, $ssc)));
      }
      CRM_Core_Smarty::singleton()->assign("financialorg", $org["financial_account_id.contact_id.legal_name"]);
      CRM_Core_Smarty::singleton()->assign("nondeductibleamount", $contribution['non_deductible_amount']);
      if (!empty($objectRef->contribution_page_id)) {
        $customColumns = CRM_Core_BAO_Cache::getItem('ehc custom columns', 'contribution-page columns');
        $contriCustomIDs = CRM_Core_BAO_Cache::getItem('ehc custom columns', 'contribution columns');
        if (!$customColumns) {
          $customColumns = getCustomColumnsByEntity('ContributionPage');
          CRM_Core_BAO_Cache::setItem($customColumns, 'ehc custom columns', 'contribution-page columns');
        }
        if (!$contriCustomIDs) {
          $contriCustomIDs = getCustomColumnsByEntity('Contribution', TRUE);
          CRM_Core_BAO_Cache::setItem($contriCustomIDs, 'ehc custom columns', 'contribution columns');
        }
        if (!empty($customColumns) && !empty($contriCustomIDs)) {
          $tablename = key($customColumns);
          // First check if solicit and sub solicit set in Recurring Profile.
          if (!empty($objectRef->contribution_recur_id)) {
            $customColumns = getCustomColumnsByEntity('ContributionRecur');
            $tablename = key($customColumns);
            $result = CRM_Core_DAO::executeQuery(sprintf("SELECT %s FROM %s WHERE entity_id = %d ",implode(',', $customColumns[$tablename]), $tablename, $objectRef->contribution_recur_id))->fetchAll();
          }
          if (empty($result)) {
            $result = CRM_Core_DAO::executeQuery(sprintf("SELECT %s FROM %s WHERE entity_id = %d ",implode(',', $customColumns[$tablename]), $tablename, $objectRef->contribution_page_id))->fetchAll();
          }
          $params = array('id' => $objectRef->id);
          foreach ($result as $value) {
            foreach ($customColumns as $tableName => $keys) {
              $params['custom_' . $contriCustomIDs['Contribution']['sc']] = $value[$keys['sc']];
              $params['custom_' . $contriCustomIDs['Contribution']['ssc']] = $value[$keys['ssc']];

              // Add finance team columns.
              $ftName = CRM_Contribute_PseudoConstant::financialType($objectRef->financial_type_id);
              $sc = CRM_Core_DAO::singleValueQuery("SELECT name FROM civicrm_option_value WHERE value = '" . $values[$keys['sc']] . "' AND option_group_id = 96");
              $ssc = CRM_Core_DAO::singleValueQuery("SELECT name FROM civicrm_option_value WHERE value = '" . $values[$keys['ssc']] . "' AND option_group_id = 97");
              $params['custom_269'] = getFinanceColumn($ftName, $sc, $ssc);
            }
          }
          civicrm_api3('Contribution', 'create', $params);
        }
      }
    }
  }
}

function getFinanceColumn($ft, $sc, $ssc) {
  switch($ft) {
    case 'General':
      if (strpos($sc, 'C4') !== false) {
        $fc = 'EHJC';
      }
      elseif (in_array($ssc, ['Staff Donation', 'Recurring Monthly Donations'])) {
        $fc = 'Recurring';
      }
    case 'Appeal':
      if ((strpos($sc, 'Mid Year') !== false) || (strpos($sc, 'Year End') !== false) || in_array($ssc, ['Organizers efforts', 'Online Contribution', 'Mailer', 'Phone Banking'])) {
        $fc = 'Individual';
      }
    case 'Special Events':
      if (strpos($sc, 'C4') !== false) {
        $fc = 'EHJC';
      }
      if (in_array($scc, ['Sponsor', 'Auction Item', 'In Kind'])) {
        $fc = 'Sponsorship';
      }
      if (strpos($sc, 'Ticket') !== false) {
        $fc = 'Events';
      }
    default:
      break;
  }
  return $fc;
}

function ehc_civicrm_postProcess($formName, &$form) {
  $domainId = CRM_Core_Config::domainID();
  if ($formName == 'CRM_Profile_Form_Edit') {
    $contactID = $form->getVar('_id');
    $profileID = $form->getVar('_gid');
    $userID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFMatch', $contactID, 'uf_id', 'contact_id');
    $matchingParams = array(
      SALTA_SIGNUP_PROFILE_ID => array(
        'role' => SALTA_USER_ROLE_ID,
        'template' => 'SALTA Signup welcome template',
        'activity_type' => 'SALTA Signup',
      ),
      HN_SIGNUP_PROFILE_ID => array(
        'role' => HN_USER_ROLE_ID,
        'template' => 'Healthy NeighBourhoods Signup welcome template',
        'activity_type' => 'Healthy Neighbourhoods Signup',
      ),
    );
    if (array_key_exists($profileID, $matchingParams)) {
      civicrm_api3('Activity', 'create', array(
        'activity_type_id' => $matchingParams[$profileID]['activity_type'],
        'target_contact_id' => $contactID,
        'source_contact_id' => CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Domain', $domainId, 'contact_id'),
      ));

      // add to joomla user role
      jimport('joomla.user.helper');
      $userObj = JFactory::getUser($userID);
      $params = array('groups' => array($matchingParams[$profileID]['role']), 'block' => 1);
      $userObj->bind($params);
      $userObj->save();

      $SALTATemplateID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_MessageTemplate', $matchingParams[$profileID]['template'], 'id', 'msg_title');
      civicrm_api3('Email', 'send', array(
        'contact_id' => $contactID,
        'template_id' => $SALTATemplateID,
      ));
    }
  }
  if (in_array($formName, array(
      'CRM_Contribute_Form_Contribution_Confirm',
      'CRM_Event_Form_Registration_Confirm',
      'CRM_Campaign_Form_Petition_Signature'
    )
  )) {
    $contactID = (!empty($form->_contactID)) ? $form->_contactID : ((!empty($form->_contactId)) ? $form->_contactId : NULL);
    $activityType = CRM_Utils_Array::value('custom_' . CF_ACTIVITY_TYPE, $form->_submitValues);
    if ($formName == 'CRM_Event_Form_Registration_Confirm') {
      $contactID = CRM_Utils_Array::value('contactID', $form->getVar('_params'));
      $activityType = CRM_Utils_Array::value('custom_' . CF_ACTIVITY_TYPE, $form->getVar('_params'));
    }

    if ($contactID && $activityType) {
      civicrm_api3('Activity', 'create', array(
        'activity_type_id' => $activityType,
        'target_contact_id' => $contactID,
	       'source_contact_id' => CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Domain', $domainId, 'contact_id'),
      ));
    }
  }
  if ($formName == 'CRM_Contribute_Form_ContributionPage_Settings' && ($id = $form->getVar('_id'))) {
    $customValues = CRM_Core_BAO_CustomField::postProcess($form->_submitValues, $id, 'ContributionPage');
    if (!empty($customValues) && is_array($customValues)) {
      CRM_Core_BAO_CustomValueTable::store($customValues, 'civicrm_contribution_page', $id);
    }
  }
}

function ehc_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  if ($formName == "CRM_Event_Form_Registration_Register" && $form->_eventId == 215) {
    if (empty($fields['price_30']) && empty($fields['price_32']) && empty($fields['price_34']) && empty($fields['price_35'])) {
//&& empty($fields['price_52']) && empty($fields['price_53'])) {
      $errors['first_name'] = ts('Please select atleast one ticket from the options below');
    }
  }
}

function ehc_civicrm_buildForm($formName, &$form) {
  if (in_array($formName, array("CRM_Contribute_Form_Contribution_Main", "CRM_Event_Form_Registration_Register"))) {
    CRM_Core_Resources::singleton()->addStyleFile('biz.jmaconsulting.ehc', 'templates/css/dpo.css', 0, 'html-header');
    if ($formName == 'CRM_Event_Form_Registration_Register' && in_array($form->_eventId, [215, 216, 217])) {
      CRM_Core_Resources::singleton()->addScript(
        "CRM.$(function($) {
          $('#amount_sum_label').text(ts('Total') + ':');
        });
        "
      );
    }
    if ($formName == 'CRM_Contribute_Form_Contribution_Main' && in_array($form->_id, array(4,5,6))) {
      CRM_Core_Resources::singleton()->addScriptFile('biz.jmaconsulting.ehc', 'templates/js/dpo.js');
    }
    /* if ($formName == "CRM_Event_Form_Registration_Register" && $form->_eventId == 215) {
      $form->add('checkbox', 'split_payment', ts('Would you like to split this payment?'));
        CRM_Core_Region::instance('page-body')->add(array(
          'template' => 'CRM/SplitPayment.tpl',
      ));
    } */
  }
  if (in_array(
    $formName,
    array(
      'CRM_Contribute_Form_Contribution_Main',
      'CRM_Contribute_Form_Contribution_Confirm',
      'CRM_Contribute_Form_Contribution_ThankYou',
    )
  )) {
    if ($form->_id != PREMIUM_CONTRIBUTION_PAGE) {
      return FALSE;
    }
    $option = '';
    if ($formName != 'CRM_Contribute_Form_Contribution_Main') {
      $priceFieldValue = $form->_params['price_27'];
      $name = $form->_priceSet['fields'][27]['options'][$priceFieldValue]['name'];
      if (strstr($name, '_two_')) {
        $option = 'show';
      }
      else{
        $option = 'hide';
      }
    }
    else {
      $form->setDefaults(array('selectProduct' => 8));
    }

    CRM_Core_Resources::singleton()->addSetting(
      ['showHideOption' => $option]
    );
    CRM_Core_Resources::singleton()->addScriptFile('biz.jmaconsulting.ehc', 'templates/CRM/js/premiums.js');
  }
  elseif ($formName == 'CRM_Contribute_Form_ContributionPage_Settings') {
    CRM_Core_Region::instance('contribute-form-contributionpage-settings-main')->add(array(
      'template' => __DIR__ . '/templates/CRM/Form/ContributionPageCustom.tpl',
    ));
  }
  elseif ($formName == 'CRM_Profile_Form_Edit') {
    CRM_Core_Resources::singleton()->addStyle('#editrow-custom_267 .label { display: none; }');
  }
  if ($formName == 'CRM_Contribute_Form_Contribution' && $form->getVar('_formType') == 'AdditionalDetail') {
    $form->setDefaults(['contribution_page_id' => 5]);
  }
  if ($formName == 'CRM_Contribute_Form_Contribution') {
    CRM_Core_Region::instance('page-body')->add(array(
      'template' => 'CRM/AddCheckDate.tpl',
    ));
  }
  if ($formName == 'CRM_Contribute_Form_ContributionView') {
    if ($form->_action & CRM_Core_Action::VIEW) {
      $contributionId = $form->get('id');
      $result = civicrm_api3('Contribution', 'get', [
        'sequential' => 1,
        'return' => ["custom_279"],
        'id' => $contributionId,
      ]);
      if (!empty($result['values'][0]['custom_279'])) {
        $checkDate = $result['values'][0]['custom_279'];
        $form->assign('checkDate', $checkDate);
      }
    }
    CRM_Core_Region::instance('page-body')->add(array(
      'template' => 'CRM/ShowCheckDate.tpl',
    ));
  }
  // Sub Activity
  if ($formName == "CRM_Activity_Form_Activity") {
    if ($form->_action & CRM_Core_Action::VIEW) {
      $form->assign('isView', TRUE);
    }
    CRM_Core_Region::instance('page-body')->add(array(
      'template' => 'CRM/AddSubActivity.tpl',
    ));
  }
}

function ehc_civicrm_tokens(&$tokens) {
  $tokens['event'] = array(
    'event.contact_name' => 'Event Contact - Name',
    'event.contact_email' => 'Event Contact - Email Address',
  );
}

function ehc_civicrm_tokenValues(&$values, $cids, $job = null, $tokens = array(), $context = null) {
  if (!empty($tokens['event'])) {
    $event = CRM_Core_Smarty::singleton()->get_template_vars('event');
    if (!empty($event['id'])) {
      $eventContacts = CRM_Core_DAO::executeQuery("SELECT confirm_from_name, confirm_from_email FROM civicrm_event WHERE id = %1", [1 => [$event['id'], "Integer"]])->fetchAll();
      if (!empty($eventContacts[0])) {
        $confirmFromName = $eventContacts[0]['confirm_from_name'];
        $confirmFromEmail = $eventContacts[0]['confirm_from_email'];
      }
      if (empty($confirmFromEmail)) {
        $confirmFromEmail = "EHC_Action_Alert@environmentalhealth.org";
      }
      if (empty($confirmFromName)) {
        $confirmFromName = "Environmental Health Coalition";
      }
      foreach ($cids as $cid) {
        $values[$cid]['event.contact_name'] = $confirmFromName;
        $values[$cid]['event.contact_email'] = $confirmFromEmail;
      }
    }
  }
}

function ehc_civicrm_alterMailParams(&$params, $context){
  if (in_array($context, ['civimail', 'flexmailer', 'messageTemplate'])) {
    $params['html'] = str_replace('https://///','https://',$params['html']);
  }
  if (!empty($params['valueName'])
    && $params['valueName'] == 'contribution_online_receipt'
    && !empty($params['tplParams']['contributionPageId'])
    && $params['tplParams']['contributionPageId'] == PREMIUM_CONTRIBUTION_PAGE
  ) {
    $tplParams = $params['tplParams'];
    $priceFieldValueId = reset($tplParams['lineItem']);
    $priceFieldValueId = reset($priceFieldValueId);

    if (in_array($priceFieldValueId['price_field_value_id'], array(77, 79))) {
      foreach ($tplParams['customPost'] as $key => $value) {
        if (!(strstr($key, 'Person 1'))) {
          unset($tplParams['customPost'][$key]);
        }
      }
    }
    $params['tplParams'] = $tplParams;
  }
}

function ehc_civicrm_alterReportVar($varType, &$var, &$object) {
  // Donation report
  if ($varType == 'columns' && get_class($object) == 'CRM_Report_Form_Contribute_Detail') {
    $var['civicrm_contact']['fields'] += array(
      'is_deceased' => array(
        'title' => ts('Contact is deceased?'),
      ),
      'do_not_email' => array(
        'title' => ts('Do Not Email'),
      ),
      'do_not_phone' => array(
        'title' => ts('Do Not Call'),
      ),
    );
    $var['civicrm_contribution']['fields'] += array(
      'contribution_recur_id' => array(
        'title' => ts('Recurring Contribution?'),
        'dbAlias' => 'IF (contribution_recur_id, "Recurring", "")',
      ),
    );
    $var['civicrm_contribution']['filters'] += array(
      'contribution_recur_id' => array(
        'title' => ts('Recurring Contributions'),
      ),
    );
    $var['civicrm_contact']['filters'] += array(
      'is_deceased' => array(
        'title' => ts('Contact has deceased?'),
      ),
      'do_not_email' => array(
        'title' => ts('Do Not Email'),
      ),
      'do_not_phone' => array(
        'title' => ts('Do Not Phone'),
      ),
    );
    $var['civicrm_contribution']['fields']['last_receive_date'] = [
      'title' => ts('Last Gift Date'),
      'dbAlias' => 'last_detail_temp.l_receive_date',
    ];
    $var['civicrm_contribution']['fields']['last_total_amount'] = [
      'title' => ts('Last Gift Amount'),
      'dbAlias' => 'last_detail_temp.l_total_amount',
    ];
    $var['civicrm_contribution']['fields']['last_financial_type_id'] = [
      'title' => ts('Last Gift Financial Type'),
      'dbAlias' => 'last_detail_temp.l_financial_type_id',
    ];
    $var['civicrm_contribution']['fields']['last_solicit_code'] = [
      'title' => ts('Last Gift Solicit Code'),
      'dbAlias' => 'last_detail_temp.l_solicit_code',
    ];
    $var['civicrm_contribution']['fields']['last_sub_solicit_code'] = [
      'title' => ts('Last Gift Sub Solicit Code'),
      'dbAlias' => 'last_detail_temp.l_sub_solicit_code',
    ];
    $var['civicrm_contribution']['fields']['last_contribution_id'] = [
      'title' => ts('Last Contribution ID'),
      'dbAlias' => 'last_detail_temp.l_id',
    ];
/*    $var['civicrm_contribution']['fields']['secondary_phone'] = [
      'title' => ts('Donor Secondary Phone'),
      'dbAlias' => 'p.phone SEPARATOR "<br/>\n"',
    ];
    $var['civicrm_contribution']['fields']['secondary_email'] = [
      'title' => ts('Donor Secondary Email'),
      'dbAlias' => 'e.email SEPARATOR "<br/>\n"',
    ]; */
    $var['civicrm_contribution']['fields']['sum_amount'] = [
      'title' => ts('Aggregate Amount'),
      'dbAlias' => 'agg_details_temp.sum_amount',
    ];
    $var['civicrm_contribution']['fields']['avg_amount'] = [
      'title' => ts('Average Amount'),
      'dbAlias' => 'agg_details_temp.avg_amount',
    ];
    $var['civicrm_contribution']['fields']['max_amount'] = [
      'title' => ts('Largest Gift Amount'),
      'dbAlias' => 'agg_details_temp.largest_gift_amount',
    ];
    $var['civicrm_contribution']['fields']['first_gift_date'] = [
      'title' => ts('First Gift Date'),
      'dbAlias' => 'agg_details_temp.first_gift_date',
    ];
    $var['civicrm_contribution']['fields']['number_of_donations'] = [
      'title' => ts('Number of Donations'),
      'dbAlias' => 'agg_details_temp.number_of_donations',
    ];
    $var['civicrm_contribution']['fields']['fy_amount'] = [
      'title' => ts('Fiscal Year Amounts'),
      'dbAlias' => 'fy_temp.fy_amounts',
    ];
    $var['civicrm_contribution']['fields']['cal_amount'] = [
      'title' => ts('Calendar Year Amounts'),
      'dbAlias' => 'cal_temp.cal_amounts',
    ];
  }
  if ($varType == 'sql' && get_class($object) == 'CRM_Report_Form_Contribute_Detail') {
    $select  = $object->getVar('_select');
    $select = str_replace('sum(contribution_civireport.total_amount) as civicrm_contribution_total_amount_sum', 'contribution_civireport.total_amount as civicrm_contribution_total_amount_sum', $select);
    $select  = $object->setVar('_select', $select);
    $from = $object->getVar('_from');
    $where = $object->getVar('_where');
    /* $sql = "CREATE TEMPORARY TABLE civireport_contribution_last_detail_temp DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci AS
    SELECT * FROM (
    SELECT now.id, last.id as l_id, last.receive_date FROM civicrm_contribution last
      LEFT JOIN
        (SELECT contribution_civireport.id, contribution_civireport.contact_id,
          contribution_civireport.receive_date
         FROM civicrm_contact contact_civireport
      INNER JOIN civicrm_contribution contribution_civireport
        ON contact_civireport.id = contribution_civireport.contact_id
        AND contribution_civireport.is_test = 0
      ) AS now ON now.contact_id = last.contact_id
        AND now.id <> last.id AND now.receive_date > last.receive_date
      WHERE now.id IS NOT NULL
      ORDER BY now.id, last.receive_date DESC
     ) as t
     GROUP BY t.id";
CRM_Core_Error::debug('ag', $sql); */
   /*
    $sql = "CREATE TEMPORARY TABLE civireport_contribution_last_detail_temp DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci AS
      SELECT
        contact_id,
        receive_date AS l_receive_date,
        total_amount AS l_total_amount,
        gd.solicit_code_256 AS l_solicit_code,
        gd.sub_solicit_code_257 AS l_sub_solicit_code,
        financial_type_id AS l_financial_type_id
        FROM civicrm_contribution c1
        LEFT JOIN civicrm_value_dp_gift_details_61 gd ON gd.entity_id = c1.id
        WHERE receive_date = (SELECT max(receive_date) FROM civicrm_contribution c2 WHERE c2.contact_id = c1.contact_id)"; */
    $sql = "
    CREATE TEMPORARY TABLE civireport_contribution_last_detail_temp DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci AS
    SELECT * FROM (select @i:=0) as a, (
      SELECT now.id, last.id as l_id, last.receive_date AS l_receive_date, last.total_amount as l_total_amount, gd.solicit_code_256 AS l_solicit_code, gd.sub_solicit_code_257 AS l_sub_solicit_code, financial_type_id AS l_financial_type_id FROM civicrm_contribution last
      LEFT JOIN civicrm_value_dp_gift_details_61 gd ON gd.entity_id = last.id
      LEFT JOIN
        (SELECT contribution_civireport.id, contribution_civireport.contact_id,
          contribution_civireport.receive_date
        {$from}
        {$where}
      ) AS now ON now.contact_id = last.contact_id
        AND now.id <> last.id AND now.receive_date > last.receive_date
      WHERE now.id IS NOT NULL
      ORDER BY now.id, last.receive_date DESC
    ) AS t
    GROUP BY t.id";
    CRM_Core_DAO::executeQuery($sql);
    $sql = "CREATE TEMPORARY TABLE civireport_contribution_aggregates_temp DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci AS SELECT c.contact_id, SUM(c.total_amount) as sum_amount, AVG(c.total_amount) as avg_amount, CONCAT('$ ', MAX(total_amount)) as largest_gift_amount, MIN(receive_date) as first_gift_date, COUNT(c.id) as number_of_donations FROM civicrm_contribution c WHERE c.contribution_status_id = 1 GROUP BY c.contact_id";
    CRM_Core_DAO::executeQuery($sql);
    $sql = "CREATE TEMPORARY TABLE civireport_contribution_fy_temp DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci AS SELECT
   CASE WHEN MONTH(receive_date)>=7 THEN
          concat(YEAR(receive_date), '-',YEAR(receive_date)+1)
   ELSE concat(YEAR(receive_date)-1,'-', YEAR(receive_date)) END AS financial_year,
   SUM(total_amount) as total_amount_sum, contact_id
FROM civicrm_contribution WHERE contribution_status_id = 1
GROUP BY contact_id, financial_year";
    CRM_Core_DAO::executeQuery($sql);
    $sql = "CREATE TEMPORARY TABLE civireport_contribution_join_temp DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci AS
      SELECT GROUP_CONCAT(concat(financial_year, ' - $ ', total_amount_sum) SEPARATOR '<br/>\n') as fy_amounts, contact_id FROM civireport_contribution_fy_temp group by contact_id
    ";
    CRM_Core_DAO::executeQuery($sql);
    $sql = "CREATE TEMPORARY TABLE civireport_contribution_cal_temp DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci AS SELECT
          YEAR(receive_date) AS cal_year,
   SUM(total_amount) as total_amount_sum, contact_id
FROM civicrm_contribution WHERE contribution_status_id = 1
GROUP BY contact_id, cal_year";
    CRM_Core_DAO::executeQuery($sql);
    $sql = "CREATE TEMPORARY TABLE civireport_contribution_cal_join_temp DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci AS
      SELECT GROUP_CONCAT(concat(cal_year, ' - $ ', total_amount_sum) SEPARATOR '<br/>\n') as cal_amounts, contact_id FROM civireport_contribution_cal_temp group by contact_id
    ";
    CRM_Core_DAO::executeQuery($sql);

    //$_aclFrom = " LEFT JOIN civireport_contribution_last_detail_temp last_detail_temp ON last_detail_temp.contact_id = contribution_civireport.contact_id ";
    $_aclFrom = " LEFT JOIN civireport_contribution_last_detail_temp last_detail_temp ON last_detail_temp.id = contribution_civireport.id
      LEFT JOIN civireport_contribution_aggregates_temp agg_details_temp ON agg_details_temp.contact_id = contribution_civireport.contact_id
      LEFT JOIN civireport_contribution_join_temp fy_temp ON fy_temp.contact_id = contribution_civireport.contact_id
      LEFT JOIN civireport_contribution_cal_join_temp cal_temp ON cal_temp.contact_id = contribution_civireport.contact_id ";
    $from .= $_aclFrom;
    $from .= " LEFT JOIN civicrm_phone p ON p.contact_id = contact_civireport.id AND p.is_primary <> 1";
    $from .= " LEFT JOIN civicrm_email e ON e.contact_id = contact_civireport.id AND e.is_primary <> 1 " . ($object->isTableSelected('civicrm_email') ? ' AND e.email <> email_civireport.email' : '');
    $object->setACLFromForLastContribution = $object->getVar('_aclFrom');
    $aclFrom = $object->getVar('_aclFrom') . $_aclFrom;

    $object->setVar('_from', $from);
    $object->setVar('_aclFrom', $aclFrom);
    $object->setFromForLastContribution = $from;
  }
  if ($varType == 'rows' && get_class($object) == 'CRM_Report_Form_Contribute_Detail') {
    $object->setVar('_aclFrom', $object->setACLFromForLastContribution);
    $object->setVar('_from', $object->setFromForLastContribution);
    $contributionTypes = CRM_Contribute_PseudoConstant::financialType();
    $solicit = CRM_Core_OptionGroup::values('solicit_code');
    $subsolicit = CRM_Core_OptionGroup::values('sub_solicit_code');
    foreach ($var as $key => $row) {
      if ($value = CRM_Utils_Array::value('civicrm_contribution_last_financial_type_id', $row)) {
        $var[$key]['civicrm_contribution_last_financial_type_id'] = $contributionTypes[$value];
      }
      if ($value = CRM_Utils_Array::value('civicrm_contribution_last_solicit_code', $row)) {
        $var[$key]['civicrm_contribution_last_solicit_code'] = $solicit[$value];
      }
      if ($value = CRM_Utils_Array::value('civicrm_contribution_last_sub_solicit_code', $row)) {
        $var[$key]['civicrm_contribution_last_sub_solicit_code'] = $subsolicit[$value];
      }
      if ($value = CRM_Utils_Array::value('civicrm_contribution_last_total_amount', $row)) {
        $var[$key]['civicrm_contribution_last_total_amount'] = CRM_Utils_Money::format($value, $row['civicrm_contribution_currency']);
      }
      if ($value = CRM_Utils_Array::value('civicrm_contribution_sum_amount', $row)) {
        $var[$key]['civicrm_contribution_sum_amount'] = CRM_Utils_Money::format($value, $row['civicrm_contribution_currency']);
      }
      if ($value = CRM_Utils_Array::value('civicrm_contribution_avg_amount', $row)) {
        $var[$key]['civicrm_contribution_avg_amount'] = CRM_Utils_Money::format($value, $row['civicrm_contribution_currency']);
      }
    }
  }

}
