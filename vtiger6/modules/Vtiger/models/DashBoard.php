<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_DashBoard_Model extends Vtiger_Base_Model {

	/**
	 * Function to get Module instance
	 * @return <Vtiger_Module_Model>
	 */
	public function getModule() {
		return $this->module;
	}

	/**
	 * Function to set the module instance
	 * @param <Vtiger_Module_Model> $moduleInstance - module model
	 * @return Vtiger_DetailView_Model>
	 */
	public function setModule($moduleInstance) {
		$this->module = $moduleInstance;
		return $this;
	}

	/**
	 *  Function to get the module name
	 *  @return <String> - name of the module
	 */
	public function getModuleName(){
		return $this->getModule()->get('name');
	}

	/**
	 * Function returns the list of Widgets
	 * @return <Array of Vtiger_Widget_Model>
	 */
	public function getSelectableDashboard() {
		$defaultWidgets = array('History', 'Upcoming Activities');
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$moduleModel = $this->getModule();

		$params = array('DASHBOARDWIDGET', $moduleModel->getId(), $currentUser->getId());
		$params = array_merge($params, $defaultWidgets);
		$result = $db->pquery('SELECT * FROM vtiger_links WHERE linktype = ?
					AND tabid = ? AND linkid NOT IN (SELECT linkid FROM vtiger_module_dashboard_widgets
					WHERE userid = ?) AND linklabel NOT IN ('.generateQuestionMarks($defaultWidgets).')', $params);
		$widgets = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$widgets[] = Vtiger_Widget_Model::getInstanceFromValues($row);
		}
		return $widgets;
	}

	/**
	 * Function returns List of User's selected Dashboard Widgets
	 * @return <Array of Vtiger_Widget_Model>
	 */
	public function getDashboards() {
		$defaultWidgets = array('History', 'Upcoming Activities');
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$moduleModel = $this->getModule();

		// NOTE: UNION might pose limitation if we need more data from vtiger_module_dashboard_widgets
		// Review required on the schema structure or merging the default and user widgets.
		$sql = "SELECT vtiger_links.*, {$currentUser->getId()} as userid, vtiger_links.linkid as id FROM vtiger_links WHERE tabid = ? AND linklabel IN (".generateQuestionMarks($defaultWidgets).")".
				" UNION ". 
				" SELECT vtiger_links.*, vtiger_module_dashboard_widgets.userid, vtiger_links.linkid as id FROM vtiger_links ".
				" INNER JOIN vtiger_module_dashboard_widgets ON vtiger_links.linkid=vtiger_module_dashboard_widgets.linkid".
				" WHERE (vtiger_module_dashboard_widgets.userid = ? AND linktype = ? AND tabid = ?)";
		
		$params = array($moduleModel->getId());
		$params = array_merge($params, $defaultWidgets);
		$params = array_merge($params, array($currentUser->getId(), 'DASHBOARDWIDGET', $moduleModel->getId()));
		
		$result = $db->pquery($sql, $params);
		
		$widgets = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$row['linkid'] = $row['id'];
			$widgets[] = Vtiger_Widget_Model::getInstanceFromValues($row);
		}
		return $widgets;
	}

	/**
	 * Function to get the default widgets(Deprecated)
	 * @return <Array of Vtiger_Widget_Model>
	 */
	public function getDefaultWidgets() {
		//TODO: Need to review this API is needed?
		$moduleModel = $this->getModule();
		$widgets = array();

		$widgets[] = array(
			'title' => 'History',
			'mode' => 'open',
			'url' => "module=". $moduleModel->getName()."&view=ShowWidget&mode=History",
		);

		$widgets[] = array(
			'title' => 'Upcoming Tasks',
			'mode' => 'open',
			'url' => 'module='. $moduleModel->getName().'&view=ShowWidget&mode=UpcomingActivities',
		);

		$widgetList= array();
		foreach($widgets as $widget) {
			$widgetList[] = Vtiger_Widget_Model::getInstanceFromValues($widget);
		}

		return $widgetList;
	}


	/**
	 * Function to get the instance
	 * @param <String> $moduleName - module name
	 * @return <Vtiger_DashBoard_Model>
	 */
	public static function getInstance($moduleName) {
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'DashBoard', $moduleName);
		$instance = new $modelClassName();

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		return $instance->setModule($moduleModel);
	}

	
}