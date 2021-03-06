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
	<div id="sendEmailContainer modelContainer">
		<div class="modal-header">
			<button data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE')}">x</button>
			<h3>{vtranslate('LBL_SELECT_EMAIL_IDS', $MODULE)}</h3>
		</div>
		<form class="form-horizontal contentsBackground" id="SendEmailFormStep1" method="post" action="index.php">
			<input type="hidden" name="selected_ids" value={ZEND_JSON::encode($SELECTED_IDS)} />
			<input type="hidden" name="excluded_ids" value={ZEND_JSON::encode($EXCLUDED_IDS)} />
			<input type="hidden" name="viewname" value="{$VIEWNAME}" />
			<input type="hidden" name="module" value="{$MODULE}"/>
			<input type="hidden" name="view" value="ComposeEmail"/>
			<div class='padding10'>
				<small>{vtranslate('LBL_MUTIPLE_EMAIL_SELECT_ONE', $SOURCE_MODULE)}</small>
			</div>
			<div class='padding10'>
				<div>
					{assign var=EMAIL_FIELD_LIST value=array()}
					{foreach item=EMAIL_FIELD from=$EMAIL_FIELDS}
						{if $EMAIL_FIELD->isViewEnabled()}
							{$EMAIL_FIELD_LIST[$EMAIL_FIELD->get('name')] = vtranslate($EMAIL_FIELD->get('label'), $SOURCE_MODULE)}
						{/if}
					{/foreach}
					<div class="control-group">
						<label class="checkbox">
							<input id="selectAllEmails" type="radio" name="selectedFields" value='{ZEND_JSON::encode(array_keys($EMAIL_FIELD_LIST))}' />
							&nbsp; {vtranslate('LBL_ALL_EMAILS', $SOURCE_MODULE)}
						</label>
					</div>
					{foreach item=EMAIL_FIELD_LABEL key=EMAIL_FIELD_NAME from=$EMAIL_FIELD_LIST name=emailFieldIterator}
						<div class="control-group">
							<label class="checkbox">
								<input type="radio" class="emailField" name="selectedFields" value='{ZEND_JSON::encode(array($EMAIL_FIELD_NAME))}' {if $smarty.foreach.emailFieldIterator.iteration eq 1} checked="checked" {/if}/>
								&nbsp; {$EMAIL_FIELD_LABEL}
							</label>
						</div>
					{/foreach}
				</div>
			</div>
			<div class='modal-footer'>
				<div class=" pull-right cancelLinkContainer">
					<a class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
				</div>
				<button class="btn addButton" type="submit" name="selectfield"><strong>{vtranslate('LBL_SELECT', $MODULE)}</strong></button>
			</div>
		</form>
	</div>
{/strip}

