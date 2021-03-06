{*<!--
/************************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*************************************************************************************/
-->*}
{strip}
{include file="Header.tpl"|vtemplate_path:$MODULE}
<div class=" page-container">
	<div class="row">
		<div class="col-md-6">
			<div class="logo">
				<img src="{\App\Layout::getImagePath('vt1.png')}" alt="Vtiger Logo"/>
			</div>
		</div>
		<div class="col-md-6">
			<div class="head pull-right">
				<h3>{\App\Language::translate('LBL_MIGRATION_WIZARD', $MODULE)}</h3>
			</div>
		</div>
	</div>
	<div class="row main-container">
		<div class="col-md-12 inner-container">
			<div class="row">
				<div id="running" class="alignCenter">
					<br /><br /><br /><br /><br />
					<h4> {\App\Language::translate('LBL_WAIT',$MODULE)} </h4><br />
						<img src="{\App\Layout::getImagePath('migration_loading.gif')}"/>
					<h5> {\App\Language::translate('LBL_INPROGRESS',$MODULE)} </h5>
				</div>
				<div id="success" class="hide">
					<h4> {\App\Language::translate('LBL_DATABASE_CHANGE_LOG',$MODULE)} </h4><hr>
				</div>
			</div>
			<div id="showDetails" style="max-height:350px; overflow: auto;">
			</div>
			<br />
			<div id="nextButton" class="button-container hide">
				<form action='index.php' method="POST">
					<input type="hidden" id="module" name="module" value="Migration">
					<input type="hidden" id="view" name="view" value="Index">
					<input type="hidden" name="mode" value="step2">
					<input type="submit" class="btn btn-lg btn-primary" value="Next"/>
				</form>
			</div>
		</div>
	</div>
</div>
