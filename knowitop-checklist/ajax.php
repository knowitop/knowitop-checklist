<?php

namespace Knowitop\iTop\Extension\Checklist;

use AjaxPage;
use DBObject;
use Dict;
use Exception;
use IssueLog;
use Knowitop\iTop\Extension\Checklist\Hook\ChecklistPlugin;
use LoginWebPage;
use MetaModel;
use UserRightException;
use utils;

require_once('../../approot.inc.php');
require_once(APPROOT.'/application/application.inc.php');
require_once(APPROOT.'/application/webpage.class.inc.php');
require_once(APPROOT.'/application/ajaxwebpage.class.inc.php');

/**
 * @param \DBObject $oObj
 *
 * @throws \ArchivedObjectException
 * @throws \CoreException
 * @throws \UserRightException
 * @throws \Exception
 */
function CheckUserRights(DBObject $oObj): void
{
	$sMsg = 'You have no rights for '.get_class($oObj).':'.$oObj->GetKey();
	if (get_class($oObj) === 'Checklist')
	{
		/** @var \Checklist $oObj */
		if (!$oObj->IsEditAllowed())
		{
			throw new UserRightException($sMsg);
		}
	}
	elseif (get_class($oObj) === 'ChecklistItem')
	{
		/** @var \Checklist $oChecklist */
		$oChecklist = MetaModel::GetObject('Checklist', $oObj->Get('checklist_id'), true);
		CheckUserRights($oChecklist);
	}
	elseif (!ChecklistPlugin::IsHostObjEditAllowed($oObj))
	{
		throw new UserRightException($sMsg);
	}
}

try
{
	require_once(APPROOT.'/application/startup.inc.php');
	require_once(APPROOT.'/application/loginwebpage.class.inc.php');
	LoginWebPage::DoLoginEx(null /* any portal */, false);

	$oPage = new AjaxPage("");
	$oPage->no_cache();

	$sOperation = utils::ReadParam('operation', '');
	$aResult = array(
		'error' => false,
		'id' => null,
		'html' => '',
		'message' => 'ok'
	);

	switch ($sOperation)
	{
		case 'remove_item':
			$iItemId = utils::ReadParam('id', 0, false, 'integer');
			/** @var \ChecklistItem $oItem */
			$oItem = MetaModel::GetObject('ChecklistItem', $iItemId);
			CheckUserRights($oItem);
			if (!is_null($oItem))
			{
				$aResult['issues'] = $oItem->DBDelete()->GetIssues();
			}
			$oPage->add(json_encode($aResult));
			break;

		case 'edit_item':
			$iItemId = utils::ReadParam('id', 0, false, 'integer');
			$sText = utils::ReadParam('text', '', false, 'string');
			/** @var \ChecklistItem $oItem */
			$oItem = MetaModel::GetObject('ChecklistItem', $iItemId, true);
			CheckUserRights($oItem);
			$oItem->Set('text', $sText);
			$aResult['id'] = $oItem->DBWrite();
			$oPage->add(json_encode($aResult));
			break;

		case 'check_item':
			$iItemId = utils::ReadParam('id', 0, false, 'integer');
			$sState = utils::ReadParam('state', '', false, 'string');
			/** @var \ChecklistItem $oItem */
			$oItem = MetaModel::GetObject('ChecklistItem', $iItemId, true);
			CheckUserRights($oItem);
			$oItem->Set('state', $sState === 'complete');
			$aResult['id'] = $oItem->DBWrite();
			$aResult['checked_at'] = $oItem->GetAsHtml('checked_at');
			$oPage->add(json_encode($aResult));
			break;

		case 'add_item':
			$iChecklistId = utils::ReadParam('checklist_id', 0, false, 'integer');
			/** @var \Checklist $oChecklist */
			$oChecklist = MetaModel::GetObject('Checklist', $iChecklistId, true); // Checklist must exists
			CheckUserRights($oChecklist);
			$sText = utils::ReadParam('text', '', false, 'string');
			$bEditMode = utils::ReadParam('edit_mode', '') === '1' || ChecklistPlugin::IsEditInPlaceAllowed();
			/** @var \ChecklistItem $oItem */
			$oItem = MetaModel::NewObject('ChecklistItem');
			$oItem->Set('checklist_id', $oChecklist->GetKey());
			$oItem->Set('text', $sText);
			$aResult['id'] = $oItem->DBWrite();
			$aResult['html'] = $oChecklist->RenderItem($oItem, $bEditMode);
			$oPage->add(json_encode($aResult));
			break;

		case 'edit_list':
			$iChecklistId = utils::ReadParam('checklist_id', 0, false, 'integer');
			/** @var \Checklist $oChecklist */
			$oChecklist = MetaModel::GetObject('Checklist', $iChecklistId, true);
			CheckUserRights($oChecklist);
			$sTitle = utils::ReadParam('title', '', false, 'string');
			$oChecklist->Set('title', $sTitle);
			$aResult['id'] = $oChecklist->DBWrite();
			$oPage->add(json_encode($aResult));
			break;

		case 'remove_list':
			$iChecklistId = utils::ReadParam('checklist_id', 0, false, 'integer');
			/** @var \Checklist $oChecklist */
			$oChecklist = MetaModel::GetObject('Checklist', $iChecklistId);
			CheckUserRights($oChecklist);
			if (!is_null($oChecklist))
			{
				$aResult['issues'] = $oChecklist->DBDelete()->GetIssues();
			} // quiet if the list already removed
			$oPage->add(json_encode($aResult));
			break;

		case 'create_list':
			$iHostId = utils::ReadParam('host_id', 0, false, 'integer');
			$sHostClass = utils::ReadParam('host_class', '', false, 'class');
			$oHostObj = MetaModel::GetObject($sHostClass, $iHostId, true); // Host object must exists
			CheckUserRights($oHostObj);
			$bEditMode = utils::ReadParam('edit_mode', '') === '1' || ChecklistPlugin::IsEditInPlaceAllowed();
			/** @var \Checklist $oChecklist */
			$oChecklist = MetaModel::NewObject('Checklist');
			$oChecklist->Set('title', Dict::S('Checklist:NewChecklistTitle'));
			$oChecklist->SetHostObject($oHostObj);
			$oChecklist->DBWrite();
			$aResult['id'] = $oChecklist->GetKey();
			$aResult['html'] .= $oChecklist->Render($bEditMode);
			$oPage->add(json_encode($aResult));
			break;

		case 'create_list_from_template':
			$aSelectedTemplateIds = utils::ReadParam('selected', []);
			$iHostId = utils::ReadParam('host_id', 0, false, 'integer');
			$sHostClass = utils::ReadParam('host_class', '', false, 'class');
			$oHostObj = MetaModel::GetObject($sHostClass, $iHostId, true); // Host object must exists
			CheckUserRights($oHostObj);
			$bEditMode = utils::ReadParam('edit_mode', '') === '1';
			$aResult['id'] = [];
			foreach($aSelectedTemplateIds as $iTemplateId)
			{
				/** @var \ChecklistTemplate $oTemplate */
				$oTemplate = MetaModel::GetObject('ChecklistTemplate', $iTemplateId, false);
				if ($oTemplate !== null)
				{
					/** @var \Checklist $oChecklist */
					$oChecklist = $oTemplate->CreateTargetObject($oHostObj);
					$oChecklist->DBWrite();
					$aResult['id'][] = $oChecklist->GetKey();
					$aResult['html'] .= $oChecklist->Render($bEditMode || ChecklistPlugin::IsEditInPlaceAllowed());
				}
			}
			$oPage->add(json_encode($aResult));
			break;

		default:
			throw new Exception("Missing argument 'operation'");
	}
	$oPage->output();
} catch (Exception $e)
{
	// note: transform to cope with XSS attacks
	$aResult['error'] = true;
	$aResult['message'] = htmlentities($e->getMessage(), ENT_QUOTES, 'utf-8');
	$oPage->add(json_encode($aResult));

	IssueLog::Error($e->getMessage());
}