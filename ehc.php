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
   WHERE cg.extends = '{$entity}' AND (LOWER(cf.name) LIKE '%solicit_code%' OR LOWER(cf.name) LIKE '%sub_solicit_code%')
   "
  );
  while ($dao->fetch()) {
    $type = strstr($dao->column_name, 'sub_solicit_code') ? 'ssc' : 'sc';
    if ($getID) {
      $customColumns[$entity][$type] = $dao->cfid;
    }
    else {
      $customColumns[$dao->table_name][$type] = $dao->column_name;
    }
  }
  return $customColumns;
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
            $params['custom_' . $contriCustomIDs['Contribution']['sc']] = $value[$keys['sc']];
            $params['custom_' . $contriCustomIDs['Contribution']['ssc']] = $value[$keys['ssc']];
          }
        }
        civicrm_api3('Contribution', 'create', $params);
      }
    }
    if ($objectName == "Contribution") {
      $contribution = civicrm_api3('Contribution', 'getsingle', array(
        'return' => array("financial_type_id", "non_deductible_amount"),
        'id' => $objectId,
      ));
      $org = civicrm_api3('EntityFinancialAccount', 'getsingle', array(
        'return' => array("financial_account_id.contact_id.legal_name", "financial_account_id.contact_id.organization_name"),
        'entity_table' => "civicrm_financial_type",
        'entity_id' => $contribution["financial_type_id"],
        'options' => array('limit' => 1),
      ));
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
          $result = CRM_Core_DAO::executeQuery(sprintf(" SELECT %s FROM %s WHERE entity_id = %d ",implode(',', $customColumns[$tablename]), $tablename, $objectRef->contribution_page_id))->fetchAll();
          $params = array('id' => $objectRef->id);
          foreach ($result as $value) {
            foreach ($customColumns as $tableName => $keys) {
              $params['custom_' . $contriCustomIDs['Contribution']['sc']] = $value[$keys['sc']];
              $params['custom_' . $contriCustomIDs['Contribution']['ssc']] = $value[$keys['ssc']];
            }
          }
          civicrm_api3('Contribution', 'create', $params);
        }
      }
    }
  }
}

function ehc_civicrm_postProcess($formName, &$form) {
  if (in_array($formName, array(
      'CRM_Contribute_Form_Contribution_Confirm',
      'CRM_Event_Form_Registration_Confirm',
      'CRM_Campaign_Form_Petition_Signature'
    )
  )) {
    $contactID = (!empty($form->_contactID)) ? $form->_contactID : (!empty($form->_contactId)) ? $form->_contactId : NULL;
    if ($formName == 'CRM_Event_Form_Registration_Confirm') {
      $contactID = CRM_Utils_Array::value('contactID', $form->getVar('_params'));
    }
    if ($contactID && CRM_Utils_Array::value('custom_' . CF_ACTIVITY_TYPE, $form->_submitValues)) {
      civicrm_api3('Activity', 'create', array(
        'activity_type_id' => $form->_submitValues['custom_' . CF_ACTIVITY_TYPE],
        'source_contact_id' => CRM_Core_Session::getLoggedInContactID(),
        'target_contact_id' => $contactID,
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

function ehc_civicrm_buildForm($formName, &$form) {
  if (in_array($formName, array("CRM_Contribute_Form_Contribution_Main", "CRM_Event_Form_Registration_Register"))) {
    CRM_Core_Resources::singleton()->addStyleFile('biz.jmaconsulting.ehc', 'templates/css/dpo.css', 0, 'html-header');
    if ($formName == 'CRM_Contribute_Form_Contribution_Main' && in_array($form->_id, array(4,5,6))) {
      CRM_Core_Resources::singleton()->addScriptFile('biz.jmaconsulting.ehc', 'templates/js/dpo.js');
    }
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
}


function ehc_civicrm_alterMailParams(&$params, $context){
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
