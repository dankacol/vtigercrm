{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/
-->*}
{strip}
{assign var=MODULE_NAME value="Calendar"}
<div  class="summaryWidgetContainer">
	<div class="widget_header row-fluid">
		<span class="span8"><h3>{vtranslate('LBL_ACTIVITIES',$MODULE_NAME)}</h3></span>
		<span class="span4"><button class="btn pull-right addButton createActivity" type="button"><strong>{vtranslate('LBL_ADD',$MODULE_NAME)}</strong></button></span>
	</div>
	<div class="widget_contents">
		{if count($ACTIVITIES) neq '0'}
			{foreach item=RECORD key=KEY from=$ACTIVITIES}
				<div class="activityEntries">
					<hr>
					<input type="hidden" class="activityId" value="{$RECORD->get('activityid')}"/>
					<div><strong>{Vtiger_Util_Helper::formatDateIntoStrings($RECORD->get('date_start'), $RECORD->get('time_start'))}</strong></div>
					<div>{vtranslate($RECORD->get('activitytype'),$MODULE_NAME)} - <a href="{$RECORD->getDetailViewUrl()}">{$RECORD->get('subject')}</a></div>
					<div class="activityStatus">
					{if $RECORD->get('activitytype') eq 'Task'}
						{assign var=MODULE_NAME value=$RECORD->getModuleName()}
						<input type="hidden" class="activityModule" value="{$RECORD->getModuleName()}"/>
						<input type="hidden" class="activityType" value="{$RECORD->get('activitytype')}"/>
						<strong><span class="value">{vtranslate($RECORD->get('status'),$MODULE_NAME)}</span></strong>&nbsp&nbsp;
						<span class="editStatus cursorPointer"><i class="icon-pencil" title="{vtranslate('LBL_EDIT',$MODULE_NAME)}"></i></span>
						<span class="edit hide">
						{assign var=FIELD_MODEL value=$RECORD->getModule()->getField('taskstatus')}
						{assign var=FIELD_VALUE value=$FIELD_MODEL->set('fieldvalue', $RECORD->get('status'))}
						{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME}
						<input type="hidden" class="fieldname" value='{$FIELD_MODEL->get('name')}' data-prev-value='{$FIELD_MODEL->get('fieldvalue')}' />
						</span>
					</div>
					{else}
						{assign var=MODULE_NAME value="Events"}
						<input type="hidden" class="activityModule" value="Events"/>
						<input type="hidden" class="activityType" value="{$RECORD->get('activitytype')}"/>
						<strong><span class="value">{vtranslate($RECORD->get('eventstatus'),$MODULE_NAME)}</span></strong>&nbsp&nbsp;
						<span class="editStatus cursorPointer"><i class="icon-pencil" title="{vtranslate('LBL_EDIT',$MODULE_NAME)}"></i></span>
						<span class="edit hide">
							{assign var=FIELD_MODEL value=$RECORD->getModule()->getField('eventstatus')}
							{assign var=FIELD_VALUE value=$FIELD_MODEL->set('fieldvalue', $RECORD->get('eventstatus'))}
							{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME}
							 {if $FIELD_MODEL->getFieldDataType() eq 'multipicklist'}
								<input type="hidden" class="fieldname" value='{$FIELD_MODEL->get('name')}[]' data-prev-value='{$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}' />
							 {else}
								 <input type="hidden" class="fieldname" value='{$FIELD_MODEL->get('name')}' data-prev-value='{$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}' />
							 {/if}
						</span>
					</div>
					{/if}
				</div>
			{/foreach}
		{else}
			<div class="summaryWidgetContainer">
				<p class="textAlignCenter">{vtranslate('LBL_NO_PENDING_ACTIVITIES',$MODULE_NAME)}</p>
			</div>
		{/if}
		{if $PAGING_MODEL->isNextPageExists()}
			<div class="row-fluid">
				<div class="pull-right">
					<a href="javascript:void(0)" class="moreRecentActivities">{vtranslate('LBL_MORE',$MODULE_NAME)}..</a>
				</div>
			</div>
		{/if}
	</div>
</div>
{/strip}