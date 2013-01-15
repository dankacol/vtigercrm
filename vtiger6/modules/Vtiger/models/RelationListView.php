<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_RelationListView_Model extends Vtiger_Base_Model {

	protected $relationModel = false;
	protected $parentRecordModel = false;

	public function setRelationModel($relation){
		$this->relationModel = $relation;
		return $this;
	}

	public function getRelationModel() {
		return $this->relationModel;
	}

	public function setParentRecordModel($parentRecord){
		$this->parentRecordModel = $parentRecord;
		return $this;
	}

	public function getParentRecordModel(){
		return $this->parentRecordModel;
	}

	public function getCreateViewUrl(){
		$relationModel = $this->getRelationModel();
		$relatedModel = $relationModel->getRelationModuleModel();
		$parentRecordModule = $this->getParentRecordModel();
		$parentModule = $parentRecordModule->getModule();

		$createViewUrl = $relatedModel->getCreateRecordUrl().'&sourceModule='.$parentModule->get('name').
								'&sourceRecord='.$parentRecordModule->getId().'&relationOperation=true';

		//To keep the reference fieldname and record value in the url if it is direct relation
		if($relationModel->isDirectRelation()) {
			$relationField = $relationModel->getRelationField();
			$createViewUrl .='&'.$relationField->getName().'='.$parentRecordModule->getId();
		}
		return $createViewUrl;
	}

	public function getCreateEventRecordUrl(){
		$relationModel = $this->getRelationModel();
		$relatedModel = $relationModel->getRelationModuleModel();
		$parentRecordModule = $this->getParentRecordModel();
		$parentModule = $parentRecordModule->getModule();

		$createViewUrl = $relatedModel->getCreateEventRecordUrl().'&sourceModule='.$parentModule->get('name').
								'&sourceRecord='.$parentRecordModule->getId().'&relationOperation=true';

		//To keep the reference fieldname and record value in the url if it is direct relation
		if($relationModel->isDirectRelation()) {
			$relationField = $relationModel->getRelationField();
			$createViewUrl .='&'.$relationField->getName().'='.$parentRecordModule->getId();
		}
		return $createViewUrl;
	}

	public function getCreateTaskRecordUrl(){
		$relationModel = $this->getRelationModel();
		$relatedModel = $relationModel->getRelationModuleModel();
		$parentRecordModule = $this->getParentRecordModel();
		$parentModule = $parentRecordModule->getModule();

		$createViewUrl = $relatedModel->getCreateTaskRecordUrl().'&sourceModule='.$parentModule->get('name').
								'&sourceRecord='.$parentRecordModule->getId().'&relationOperation=true';

		//To keep the reference fieldname and record value in the url if it is direct relation
		if($relationModel->isDirectRelation()) {
			$relationField = $relationModel->getRelationField();
			$createViewUrl .='&'.$relationField->getName().'='.$parentRecordModule->getId();
		}
		return $createViewUrl;
	}

	public function getLinks(){
		$relationModel = $this->getRelationModel();
		$actions = $relationModel->getActions();

		$selectLinks = $this->getSelectRelationLinks();
		foreach($selectLinks as $selectLinkModel) {
			$selectLinkModel->set('_selectRelation',true)->set('_module',$relationModel->getRelationModuleModel());
		}
		$addLinks = $this->getAddRelationLinks();

		$links = array_merge($selectLinks, $addLinks);
		$relatedLink = array();
		$relatedLink['LISTVIEWBASIC'] = $links;
		return $relatedLink;
	}

	public function getSelectRelationLinks() {
		$relationModel = $this->getRelationModel();
		$selectLinkModel = array();

		if(!$relationModel->isSelectActionSupported()) {
			return $selectLinkModel;
		}

		$relatedModel = $relationModel->getRelationModuleModel();

		$selectLinkList = array(
			array(
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => vtranslate('LBL_SELECT')." ".vtranslate($relatedModel->get('label')),
				'linkurl' => '',
				'linkicon' => '',
			)
		);


		foreach($selectLinkList as $selectLink) {
			$selectLinkModel[] = Vtiger_Link_Model::getInstanceFromValues($selectLink);
		}
		return $selectLinkModel;
	}

	public function getAddRelationLinks() {
		$relationModel = $this->getRelationModel();
		$addLinkModel = array();

		if(!$relationModel->isAddActionSupported()) {
			return $addLinkModel;
		}
		$relatedModel = $relationModel->getRelationModuleModel();

		if($relatedModel->get('label') == 'Calendar'){

			$addLinkList[] = array(
					'linktype' => 'LISTVIEWBASIC',
					'linklabel' => vtranslate('LBL_ADD_EVENT'),
					'linkurl' => $this->getCreateEventRecordUrl(),
					'linkicon' => '',
			);
			$addLinkList[] = array(
					'linktype' => 'LISTVIEWBASIC',
					'linklabel' => vtranslate('LBL_ADD_TASK'),
					'linkurl' => $this->getCreateTaskRecordUrl(),
					'linkicon' => '',
			);
		}else{
			$addLinkList = array(
				array(
					'linktype' => 'LISTVIEWBASIC',
					// NOTE: $relatedModel->get('label') assuming it to be a module name - we need singular label for Add action.
					'linklabel' => vtranslate('LBL_ADD')." ".vtranslate('SINGLE_' . $relatedModel->get('label'), $relatedModel->get('label')),
					'linkurl' => $this->getCreateViewUrl(),
					'linkicon' => '',
				)
			);
		}

		foreach($addLinkList as $addLink) {
			$addLinkModel[] = Vtiger_Link_Model::getInstanceFromValues($addLink);
		}
		return $addLinkModel;
	}

	public function getEntries($pagingModel) {
		$db = PearDatabase::getInstance();
		$parentModule = $this->getParentRecordModel()->getModule();
		$relationModule = $this->getRelationModel()->getRelationModuleModel();
		$relatedColumnFields = $relationModule->getRelatedListFields();
		$query = $this->getRelationQuery();

		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();

		$orderBy = $this->getForSql('orderby');
		$sortOrder = $this->getForSql('sortorder');
		if($orderBy) {
			if($orderBy === 'assigned_user_id' || $orderBy == 'smownerid') {
				$orderBy = 'user_name';
			}
			// Qualify the the column name with table to remove ambugity
			$qualifiedOrderBy = $orderBy;
			$orderByField = $relationModule->getFieldByColumn($orderBy);
			if ($orderByField) {
				$qualifiedOrderBy = $orderByField->get('table') . '.' . $qualifiedOrderBy;
			}
			$query = "$query ORDER BY $qualifiedOrderBy $sortOrder";
		}

		$limitQuery = $query .' LIMIT '.$startIndex.','.$pageLimit;
		$result = $db->pquery($limitQuery, array());
		$relatedRecordList = array();

		for($i=0; $i< $db->num_rows($result); $i++ ) {
			$row = $db->fetch_row($result,$i);
			$newRow = array();
			foreach($row as $col=>$val){
				if(array_key_exists($col,$relatedColumnFields)){
                    $newRow[$relatedColumnFields[$col]] = $val;
                }
            }
			//To show the value of "Assigned to"
			$newRow['assigned_user_id'] = $row['smownerid'];
			$record = Vtiger_Record_Model::getCleanInstance($relationModule->get('name'));
            $record->setData($newRow)->setModuleFromInstance($relationModule);
            $record->setId($row['crmid']);
			$relatedRecordList[] = $record;
		}
		$pagingModel->calculatePageRange($relatedRecordList);

		$nextLimitQuery = $query. ' LIMIT '.($startIndex+$pageLimit).' , 1';
		$nextPageLimitResult = $db->pquery($nextLimitQuery, array());
		if($db->num_rows($nextPageLimitResult) > 0){
			$pagingModel->set('nextPageExists', true);
		}else{
			$pagingModel->set('nextPageExists', false);
		}
		return $relatedRecordList;
	}

	public function getHeaders() {
		$relationModel = $this->getRelationModel();
		$relatedModuleModel = $relationModel->getRelationModuleModel();

		$headerFieldNames = $relatedModuleModel->getRelatedListFields();

		$headerFields = array();
		foreach($headerFieldNames as $fieldName) {
			$headerFields[$fieldName] = $relatedModuleModel->getField($fieldName);
		}
		return $headerFields;
	}

	/**
	 * Function to get Relation query
	 * @return <String>
	 */
	public function getRelationQuery() {
		$relationModel = $this->getRelationModel();
		$recordModel = $this->getParentRecordModel();
		$query = $relationModel->getQuery($recordModel);
		return $query;
	}

	public static function getInstance($parentRecordModel, $relationModuleName, $label=false) {
		$parentModuleName = $parentRecordModel->getModule()->get('name');
		$className = Vtiger_Loader::getComponentClassName('Model', 'RelationListView', $parentModuleName);
		$instance = new $className();

		$parentModuleModel = $parentRecordModel->getModule();
		$relationModuleModel = Vtiger_Module_Model::getInstance($relationModuleName);

		$relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relationModuleModel, $label);
		$instance->setRelationModel($relationModel)->setParentRecordModel($parentRecordModel);
		return $instance;
	}

	/**
	 * Function to get Total number of record in this relation
	 * @return <Integer>
	 */
	public function getRelatedEntriesCount() {
		$db = PearDatabase::getInstance();
		$relationQuery = $this->getRelationQuery();
		$position = stripos($relationQuery, 'from');
		if ($position) {
			$split = spliti('from', $relationQuery);
			$splitCount = count($split);
			$relationQuery = 'SELECT count(*) AS count ';
			for ($i=1; $i<$splitCount; $i++) {
				$relationQuery = $relationQuery. ' FROM ' .$split[$i];
			}
		}
		$result = $db->pquery($relationQuery, array());
		return $db->query_result($result, 0, 'count');
	}
}
