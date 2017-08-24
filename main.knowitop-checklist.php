<?php
/**
 * Checklist plugin
 *
 * @copyright   Copyright (C) 2017 Vladimir Kunin <v.b.kunin@gmail.com>
 */

class ChecklistPlugin implements iApplicationUIExtension, iApplicationObjectExtension
{

    const MODULE_NAME = 'knowitop-checklist';

    public function OnDisplayProperties($oObject, WebPage $oPage, $bEditMode = false)
    {
    }

    public function OnDisplayRelations($oObject, WebPage $oPage, $bEditMode = false)
    {
        if ($this->IsTargetObject($oObject) && !$oObject->IsNew()) {
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

    public function EnumAllowedActions(\DBObjectSet $oSet)
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

    public function OnDBUpdate($oObject, $oChange = null)
    {
        if ($this->IsTargetObject($oObject)) {
            $oSearch = DBObjectSearch::FromOQL("SELECT Checklist WHERE obj_class = :class AND obj_key = :key");
            $oSet = new DBObjectSet($oSearch, array(), array('class' => get_class($oObject), 'key' => $oObject->GetKey()));
            while ($oChecklist = $oSet->Fetch()) {
                $oChecklist->SetHostObject($oObject, true);
            }
        }
    }

    public function OnDBInsert($oObject, $oChange = null)
    {
    }

    public function OnDBDelete($oObject, $oChange = null)
    {
        if ($this->IsTargetObject($oObject)) {
            $oSearch = DBObjectSearch::FromOQL("SELECT Checklist WHERE obj_class = :class AND obj_key = :key");
            $oSet = new DBObjectSet($oSearch, array(), array('class' => get_class($oObject), 'key' => $oObject->GetKey()));
            while ($oChecklist = $oSet->Fetch()) {
                $oChecklist->DBDelete();
            }
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////
    //
    // Plug-ins specific functions
    //
    ///////////////////////////////////////////////////////////////////////////////////////////////////////

    protected function IsTargetObject($oObject)
    {
        $aAllowedClasses = \MetaModel::GetModuleSetting(self::MODULE_NAME, 'allowed_classes', array());
        foreach ($aAllowedClasses as $sAllowedClass) {
            if ($oObject instanceof $sAllowedClass) {
                return true;
            }
        }
        return false;
    }

    public static function IsEditInPlaceAllowed()
    {
        return MetaModel::GetModuleSetting(ChecklistPlugin::MODULE_NAME, 'edit_in_place', true);
    }

    public static function IsHostObjEditAllowed($oHost)
    {
        return UserRights::IsActionAllowed(get_class($oHost), UR_ACTION_MODIFY, DBObjectSet::FromObject($oHost));
    }

    public function DisplayChecklists(\DBObject $oHost, \iTopWebPage $oPage, $bEditMode = false)
    {
        $bEditMode = $bEditMode || self::IsEditInPlaceAllowed();
        $oPage->add_linked_stylesheet(\utils::GetAbsoluteUrlModulesRoot() . self::MODULE_NAME . '/css/checklist.css');

        $oSearch = \DBObjectSearch::FromOQL("SELECT Checklist WHERE obj_class = :class AND obj_key = :obj_key");
        $oSet = new \DBObjectSet($oSearch, array(), array('class' => get_class($oHost), 'obj_key' => $oHost->GetKey()));
        // Display tab and header
        $sTabLabel = $oSet->Count() > 0 ? \Dict::Format('Checklist:TabTitle_Count', $oSet->Count()) : \Dict::S('Checklist:EmptyTabTitle');
        $oPage->SetCurrentTab($sTabLabel);
        $oPage->p(\MetaModel::GetClassIcon('Checklist') . '&nbsp;' . \Dict::S('Checklist:TabTitle+'));
        // Display checklists
        while ($oChecklist = $oSet->Fetch()) {
            $oPage->add($oChecklist->Render($bEditMode));
        }
        // Display actions if current user has rights to modify host object
        if (self::IsHostObjEditAllowed($oHost)) {
            if ($bEditMode) {
                $sNewListDiv = el('div.checklist-new-list', null, array(
                    el('a.checklist-btn', array('data-checklist-action' => 'create_list'), \Dict::S('Checklist:Button:NewList')),
                    el('a.checklist-btn', array('data-checklist-action' => 'create_list_from_template'), \Dict::S('Checklist:Button:NewListFromTmpl')),
                ));
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
            $oPage->add_linked_script("../js/wizardhelper.js");
            $sApiUrl = \utils::GetAbsoluteUrlModulesRoot() . self::MODULE_NAME . '/ajax.php';
            $iHostObjId = $oHost->GetKey();
            $sHostObjClass = get_class($oHost);

            $oPage->add_script(
 <<<EOF
    var checklistApi = new ChecklistAPI({
        url: '$sApiUrl',
        hostId: $iHostObjId,
        hostClass: '$sHostObjClass'
    });
EOF
            );
            $oPage->add_linked_script(\utils::GetAbsoluteUrlModulesRoot() . self::MODULE_NAME . '/js/checklist.js');
        }
    }
}

// TODO: Checklist history – create, rename, complete, remove...

function el($tag, $attrs = null, $contents = null)
{
    return \Knowitop\HtmlRenderer::render($tag, $attrs, $contents);
}