<?php

require_once 'ehc.civix.php';
use CRM_Ehc_ExtensionUtil as E;
define('PREMIUM_CONTRIBUTION_PAGE', 7);
/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function ehc_civicrm_config(&$config) {
  _ehc_civix_civicrm_config($config);
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

function ehc_civicrm_buildForm($formName, &$form) {
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
}
