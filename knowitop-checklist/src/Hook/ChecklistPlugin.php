<?php

namespace Knowitop\iTop\Extension\Checklist\Hook;

use DBObject;
use DBObjectSearch;
use DBObjectSet;
use Dict;
use iApplicationObjectExtension;
use iApplicationUIExtension;
use Knowitop\iTop\Extension\Checklist\Helper\SimpleHtmlRenderer as html;
use Knowitop\iTop\Extension\Checklist\ModuleConfig;
use MetaModel;
use UserRights;
use WebPage;
use WorkOrder;

/**
 * Checklist plugin
 *
 * @copyright   Copyright (C) 2019-2022 Vladimir Kunin https://knowitop.ru
 */

class ChecklistPlugin implements iApplicationUIExtension, iApplicationObjectExtension
{
	public function OnDisplayProperties($oObject, WebPage $oPage, $bEditMode = false)
	{
	}

	/**
	 * @throws \MissingQueryArgument
	 * @throws \CoreUnexpectedValue
	 * @throws \CoreException
	 * @throws \OQLException
	 * @throws \MySQLHasGoneAwayException
	 * @throws \MySQLException
	 */
	public function OnDisplayRelations($oObject, WebPage $oPage, $bEditMode = false)
	{
		if ($this->IsTargetObject($oObject) && !$oObject->IsNew())
		{
			$this->DisplayChecklists($oObject, $oPage, $bEditMode);
		}
	}

	public function OnFormSubmit($oObject, $sFormPrefix = '')
	{
	}

	public function OnFormCancel($sTempId)
	{
	}

	public function EnumUsedAttributes($oObject)
	{
		return array();
	}

	public function GetIcon($oObject)
	{
		return '';
	}

	public function GetHilightClass($oObject)
	{
		return HILIGHT_CLASS_NONE;
	}

	public function EnumAllowedActions(DBObjectSet $oSet)
	{
		return array();
	}

	public function OnIsModified($oObject)
	{
		return false;
	}

	public function OnCheckToWrite($oObject)
	{
		return array();
	}

	public function OnCheckToDelete($oObject)
	{
		return array();
	}

	/**
	 * @throws \CoreUnexpectedValue
	 * @throws \CoreException
	 * @throws \OQLException
	 * @throws \MySQLException
	 */
	public function OnDBUpdate($oObject, $oChange = null)
	{
		if ($this->IsTargetObject($oObject))
		{
			$oSet = $this->GetChecklistSetForObject($oObject);
			while ($oChecklist = $oSet->Fetch())
			{
				$oChecklist->SetHostObject($oObject, true);
			}
		}
	}

	/**
	 * @param \DBObject $oObject
	 * @param null $oChange
	 *
	 * @throws \ArchivedObjectException
	 * @throws \CoreException
	 * @throws \CoreUnexpectedValue
	 */
	public function OnDBInsert($oObject, $oChange = null)
	{
		if ($this->IsTargetObject($oObject))
		{
			if ($oObject instanceof WorkOrder && $oObject->Get('template_key') > 0)
			{
				$oWorkOrderTemplate = MetaModel::GetObject('WorkOrderTemplate', $oObject->Get('template_key'), false);
				if (is_null($oWorkOrderTemplate))
				{
					return;
				}
				/** @var \ormLinkSet $oLinkSet */
				$oLinkSet = $oWorkOrderTemplate->Get('checklist_templates_list');
				while ($oLink = $oLinkSet->Fetch())
				{
					/** @var \ChecklistTemplate $oChecklistTemplate */
					$oChecklistTemplate = MetaModel::GetObject('ChecklistTemplate', $oLink->Get('checklist_template_id'));
					if ($oChecklistTemplate->Get('status') === 'inactive') {
						continue;
					}
					$oChecklist = $oChecklistTemplate->CreateTargetObject($oObject);
					$oChecklist->DBWrite();
				}
			}
		}
	}

	/**
	 * @param \DBObject $oObject
	 * @param null $oChange
	 *
	 * @throws \CoreException
	 * @throws \CoreUnexpectedValue
	 * @throws \DeleteException
	 * @throws \MySQLException
	 * @throws \OQLException
	 */
	public function OnDBDelete($oObject, $oChange = null)
	{
		if ($this->IsTargetObject($oObject))
		{
			$oSet = $this->GetChecklistSetForObject($oObject);
			/** @var \Checklist $oChecklist */
			while ($oChecklist = $oSet->Fetch())
			{
				$oChecklist->DBDelete();
			}
		}
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////
	//
	// Plug-ins specific functions
	//
	///////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * @param \DBObject $oObject
	 *
	 * @return bool
	 */
	protected function IsTargetObject(DBObject $oObject): bool
	{
		$aAllowedClasses = ModuleConfig::Get('allowed_classes', array());
		foreach($aAllowedClasses as $sAllowedClass)
		{
			if ($oObject instanceof $sAllowedClass)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @param \DBObject $oObject
	 *
	 * @return \DBObjectSet
	 * @throws \OQLException
	 */
	protected function GetChecklistSetForObject(DBObject $oObject): DBObjectSet
	{
		$aParams = ['class' => get_class($oObject), 'key' => $oObject->GetKey()];

		return new DBObjectSet(DBObjectSearch::FromOQL("SELECT Checklist WHERE obj_class = :class AND obj_key = :key", $aParams));
	}

	/**
	 * @return bool
	 */
	public static function IsEditInPlaceAllowed(): bool
	{
		return ModuleConfig::Get('edit_in_place', true);
	}

	/**
	 * @param \DBObject $oHost
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public static function IsHostObjEditAllowed(DBObject $oHost): bool
	{
		return UserRights::IsActionAllowed(get_class($oHost), UR_ACTION_MODIFY, DBObjectSet::FromObject($oHost));
	}

	/**
	 * @param \DBObject $oHost
	 * @param \iTopWebPage $oPage
	 * @param bool $bEditMode
	 *
	 * @throws \CoreException
	 * @throws \CoreUnexpectedValue
	 * @throws \MissingQueryArgument
	 * @throws \MySQLException
	 * @throws \MySQLHasGoneAwayException
	 * @throws \OQLException
	 * @throws \Exception
	 */
	public function DisplayChecklists(DBObject $oHost, WebPage $oPage, bool $bEditMode = false): void
	{
		$bEditMode = $bEditMode || self::IsEditInPlaceAllowed();
		$oSet = $this->GetChecklistSetForObject($oHost);
		// Display tab and header
		$sTabLabel = $oSet->Count() > 0 ? Dict::Format('Checklist:TabTitle_Count', $oSet->Count()) : Dict::S('Checklist:EmptyTabTitle');
		$oPage->SetCurrentTab($sTabLabel);
		$oPage->p(MetaModel::GetClassIcon('Checklist').'&nbsp;'.Dict::S('Checklist:TabTitle+'));
		// Display checklists
		/** @var \Checklist $oChecklist */
		while ($oChecklist = $oSet->Fetch())
		{
			$oPage->add($oChecklist->Render($bEditMode));
		}
		// Display actions if current user has rights to modify host object
		if (self::IsHostObjEditAllowed($oHost))
		{
			if ($bEditMode)
			{
				$sNewListDiv = html::render('div.checklist-new-list', null, [
					html::render('a.checklist-btn', ['data-checklist-action' => 'create_list'], Dict::S('Checklist:Button:NewList')),
					html::render('a.checklist-btn', ['data-checklist-action' => 'create_list_from_template'], Dict::S('Checklist:Button:NewListFromTmpl')),
				]);
				$oPage->add($sNewListDiv);
			}

			$oPage->add_dict_entry('UI:Checklist:DlgPickATemplate');
			$oPage->add_dict_entry('UI:Checklist:DeleteDlg:Title');
			$oPage->add_dict_entry('UI:Checklist:DeleteDlg:Msg');
			$oPage->add_dict_entry('UI:Checklist:DeleteDlg:Delete');
			$oPage->add_dict_entry('UI:Checklist:DeleteDlg:Cancel');
			$oPage->add_dict_entry('UI:ChecklistItem:DeleteDlg:Title');
			$oPage->add_dict_entry('UI:ChecklistItem:DeleteDlg:Msg');
			$oPage->add_dict_entry('UI:ChecklistItem:DeleteDlg:Delete');
			$oPage->add_dict_entry('UI:ChecklistItem:DeleteDlg:Cancel');

			// Load js scripts
			$sApiUrl = ModuleConfig::GetRootUrl().'/ajax.php';
			$iHostObjId = $oHost->GetKey();
			$sHostObjClass = get_class($oHost);
			$sFilter = "SELECT ChecklistTemplate WHERE status = 'active'";
			$aParams = [
				'url' => $sApiUrl,
				'hostId' => $iHostObjId,
				'hostClass' => $sHostObjClass,
				'oqlFilter' => $sFilter
			];
			$sParamsJson = json_encode($aParams);
			$oPage->add_script("var checklistApi = new ChecklistAPI($sParamsJson);");
			$oPage->add_linked_script(ModuleConfig::GetJsAssetsUrl().'checklist.min.js');
			$oPage->add_linked_stylesheet(ModuleConfig::GetCssAssetsUrl().'checklist.min.css');
		}
	}
}
