<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */
Vtiger_Loader::includeOnce('~include/Webservices/ConvertLead.php');

class Leads_SaveConvertLead_View extends Vtiger_View_Controller
{

	/**
	 * Record model instance
	 * @var Vtiger_Record_Model 
	 */
	protected $record = false;

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermitted
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($moduleName, 'ConvertLead')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
		$this->record = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		if (!$this->record->isEditable()) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
		if (!Leads_Module_Model::checkIfAllowedToConvert($this->record->get('leadstatus'))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function preProcess(\App\Request $request, $display = true)
	{
		
	}

	public function process(\App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$modules = $request->get('modules');
		$assignId = $request->get('assigned_user_id');
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$entityValues = [];
		$entityValues['transferRelatedRecordsTo'] = $request->get('transferModule');
		$entityValues['assignedTo'] = $assignId;
		$entityValues['leadId'] = $recordId;
		$createAlways = Vtiger_Processes_Model::getConfig('marketing', 'conversion', 'create_always');

		$convertLeadFields = $this->record->getConvertLeadFields();
		$availableModules = ['Accounts'];
		foreach ($availableModules as $module) {
			if (\App\Module::isModuleActive($module) && in_array($module, $modules)) {
				$entityValues['entities'][$module]['create'] = true;
				$entityValues['entities'][$module]['name'] = $module;
				foreach ($convertLeadFields[$module] as $fieldModel) {
					$fieldName = $fieldModel->getName();
					$fieldValue = $fieldModel->getUITypeModel()->getDBValue($request->get($fieldName, null));
					$entityValues['entities'][$module][$fieldName] = $fieldValue;
				}
			}
		}
		try {
			$results = true;
			if ($createAlways === true || $createAlways === 'true') {
				$leadModel = Vtiger_Module_Model::getCleanInstance($request->getModule());
				$results = $leadModel->searchAccountsToConvert($this->record);
				$entityValues['entities']['Accounts']['convert_to_id'] = $results;
			}
			if (!$results) {
				$message = \App\Language::translate('LBL_TOO_MANY_ACCOUNTS_TO_CONVERT', $request->getModule(), '');
				if ($currentUser->isAdminUser()) {
					$message = \App\Language::translate('LBL_TOO_MANY_ACCOUNTS_TO_CONVERT', $request->getModule(), '<a href="index.php?module=MarketingProcesses&view=Index&parent=Settings"><span class="glyphicon glyphicon-folder-open"></span></a>');
				}
				$this->showError($request, '', $message);
				throw new \App\Exceptions\AppException('LBL_TOO_MANY_ACCOUNTS_TO_CONVERT');
			}
		} catch (Exception $e) {
			$this->showError($request, $e);
			throw new \App\Exceptions\AppException($e->getMessage());
		}
		try {
			$result = vtws_convertlead($entityValues, $currentUser);
		} catch (Exception $e) {
			$this->showError($request, $e);
			throw new \App\Exceptions\AppException($e->getMessage());
		}

		if (!empty($result['Accounts'])) {
			$accountId = $result['Accounts'];
		}

		if (!empty($accountId)) {
			ModTracker_Record_Model::addConvertToAccountRelation('Accounts', $accountId, $assignId);
			header("Location: index.php?view=Detail&module=Accounts&record=$accountId");
		} else {
			$this->showError($request);
			throw new \App\Exceptions\AppException('Error');
		}
	}

	public function showError($request, $exception = false, $message = '')
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$currentUser = Users_Record_Model::getCurrentUserModel();

		if ($exception !== false) {
			$viewer->assign('EXCEPTION', \App\Language::translate($exception->getMessage(), $moduleName));
		} elseif ($message) {
			$viewer->assign('EXCEPTION', $message);
		}

		$viewer->assign('CURRENT_USER', $currentUser);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('ConvertLeadError.tpl', $moduleName);
	}

	public function validateRequest(\App\Request $request)
	{
		$request->validateWriteAccess();
	}
}
