<?php
/**
 * Checklist plugin
 *
 * @copyright   Copyright (C) 2017 Vladimir Kunin <v.b.kunin@gmail.com>
 */

class ChecklistPlugin implements \iApplicationUIExtension, \iApplicationObjectExtension
{

    const MODULE_NAME = 'knowitop-checklist';

    public function OnDisplayProperties($oObject, \WebPage $oPage, $bEditMode = false) {}

    public function OnDisplayRelations($oObject, \WebPage $oPage, $bEditMode = false)
    {
        if ($this->IsTargetObject($oObject) && !$oObject->IsNew())
        {
            $this->DisplayChecklists($oObject, $oPage, $bEditMode);
        }
    }

    public function OnFormSubmit($oObject, $sFormPrefix = '')
    {
        if ($this->IsTargetObject($oObject) && !$oObject->IsNew())
        {
            self::UpdateChecklists($oObject);
        }
    }

    public function OnFormCancel($sTempId) {}

    public function EnumUsedAttributes($oObject) { return array(); }

    public function GetIcon($oObject) { return '';}

    public function GetHilightClass($oObject)
    {
        // Possible return values are:
        // HILIGHT_CLASS_CRITICAL, HILIGHT_CLASS_WARNING, HILIGHT_CLASS_OK, HILIGHT_CLASS_NONE
        return HILIGHT_CLASS_NONE;
    }

    public function EnumAllowedActions(\DBObjectSet $oSet)
    {
        // No action
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

    }

    public function OnDBInsert($oObject, $oChange = null)
    {

    }

    public function OnDBDelete($oObject, $oChange = null)
    {
        if ($this->IsTargetObject($oObject))
        {
            $oSearch = DBObjectSearch::FromOQL("SELECT Checklist WHERE obj_class = :class AND obj_key = :key");
            $oSet = new DBObjectSet($oSearch, array(), array('class' => get_class($oObject), 'key' => $oObject->GetKey()));
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

    protected function IsTargetObject($oObject)
    {
        $aAllowedClasses = \MetaModel::GetModuleSetting(self::MODULE_NAME, 'allowed_classes', array());
        foreach($aAllowedClasses as $sAllowedClass)
        {
            if ($oObject instanceof $sAllowedClass)
            {
                return true;
            }
        }
        return false;
    }

    protected static function IsEditInPlace()
    {
        return \MetaModel::GetModuleSetting(ChecklistPlugin::MODULE_NAME, 'edit_in_place', true);
    }

    public function DisplayChecklists(\DBObject $oHost, \iTopWebPage $oPage, $bEditMode = false)
    {

        $bEditMode = self::IsEditInPlace() || $bEditMode;

        $sApiUrl = \utils::GetAbsoluteUrlModulesRoot().self::MODULE_NAME.'/ajax.php';
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

        // TODO: тут проверка прав пользователя есть?
        /*
         * Чек-лист отображается на вкладке, если у пользователя есть достут на чтение хост-объекта.
         * Если доступа нет, хост-объект не будет загружен, не будет и чек-листа.
         *
         * Редактирование чек-листа
         *
         * Чек-лист доступен по прямой ссылке, когда у пользователя есть доступ
         * к org_id хоста (org_id чек-листа берется org_id хоста). С элементами чек-листа аналогично.
         */


        $oSearch = \DBObjectSearch::FromOQL("SELECT Checklist WHERE obj_class = :class AND obj_key = :obj_key");
        $oSet = new \DBObjectSet($oSearch, array(), array('class' => get_class($oHost), 'obj_key' => $oHost->GetKey()));

        $oPage->add_linked_stylesheet(\utils::GetAbsoluteUrlModulesRoot().self::MODULE_NAME.'/css/checklist.css');

        //TODO: сделать выбор: отображать кол-во чек-листов или сумму элементов на вклдаке?
        $sTabLabel = ($oSet->Count() > 0) ? \Dict::Format('Checklist:TabTitle_Count', $oSet->Count()) : \Dict::S('Checklist:EmptyTabTitle');
        $oPage->SetCurrentTab($sTabLabel);
        $oPage->p(\MetaModel::GetClassIcon('Checklist').'&nbsp;'.\Dict::S('Checklist:TabTitle+'));

        while($oChecklist = $oSet->Fetch())
        {
            //$oPage->add(self::RenderChecklist($oChecklist, $bEditMode));
            $oPage->add($oChecklist->Render($bEditMode));
        }

        if ($bEditMode && UserRights::IsActionAllowed(get_class($oHost), UR_ACTION_MODIFY)) {
            // Если пользователь имеет права на хост-объект, то он может добавлять и удалять чек-листы
            $sNewListDiv = el('div.checklist-new-list', null, array(
                el('a.checklist-btn', array('data-checklist-action' => 'create_list'), \Dict::S('Checklist:Button:NewList')),
                el('a.checklist-btn', array('data-checklist-action' => 'create_list_from_template'), \Dict::S('Checklist:Button:NewListFromTmpl')),
            ));
            $oPage->add($sNewListDiv);
        }

        // TODO: разбить скрипт на 2: для редактировани и использования чек-листов
        $oPage->add_linked_script("../js/wizardhelper.js");
        $oPage->add_linked_script(\utils::GetAbsoluteUrlModulesRoot().self::MODULE_NAME.'/js/checklist.js');
    }

    protected static function UpdateChecklists($oObject, $oChange = null)
    {
        $oSearch = DBObjectSearch::FromOQL("SELECT Checklist WHERE obj_class = :class AND obj_key = :key");
        $oSet = new DBObjectSet($oSearch, array(), array('class' => get_class($oObject), 'key' => $oObject->GetKey()));
        while ($oChecklist = $oSet->Fetch())
        {
            $oChecklist->SetHostObject($oObject, true);
        }
    }


//
//    public static function RenderChecklist(\DBObject $oChecklist, $bEditMode = false) {
//
//        $bEditMode = self::IsEditInPlace() || $bEditMode;
//
//        $iChecklistId = $oChecklist->GetKey();
//        $sChecklistName = $oChecklist->GetName();
//        $aChecklistItems = array();
//        $oItemSet = $oChecklist->Get('items_list');
//        while($oItem = $oItemSet->Fetch())
//            array_push($aChecklistItems, el('li', null, self::RenderChecklistItem($oItem, $bEditMode)));
//
//        $sChecklistActions = !$bEditMode ? '' : el('div.checklist-new-item', null, array(
//            el('a.checklist-btn.quiet', array('data-checklist-action' => 'new_item'), \Dict::S('Checklist:Button:NewItem')),
//            el('input.checklist-item-text-input', array('name' => 'newItemText', 'maxlength' => 255, 'placeholder' => \Dict::S('Checklist:NewItemPlaceholder'))),
//            el('a.checklist-btn.quiet', array('data-checklist-action' => 'remove_list'), \Dict::S('Checklist:Button:RemoveList')),
//            el('div.checklist-item-actions', null, array(
//                    el('a.checklist-btn.quiet', array('data-checklist-action' => 'save_item'), \Dict::S('Checklist:Button:Apply')),
//                    el('a.checklist-btn.quiet', array('data-checklist-action' => 'cancel_item'), \Dict::S('Checklist:Button:Cancel')))
//            )
//        ));
//
//        $sHtml = el('div.checklist', array('data-checklist-id' => $iChecklistId, 'data-checklist-name' => $sChecklistName), array(
//            el('h3.checklist-title', array('data-checklist-id'=> $iChecklistId), $sChecklistName),
//            el('ol.checklist-items', null, $aChecklistItems),
//            $sChecklistActions
//        ));
//
//        return $sHtml;
//    }
//
//    public static function RenderChecklistItem(\DBObject $oItem, $bEditMode = false)
//    {
//        // TODO: Пользователь имеет права на этот чеклист?
//        $iItemId = $oItem->GetKey();
//        $sItemText = $oItem->Get('text');
//        $sItemCheckedAt = $oItem->GetAsHTML('checked_at');
//        $sCheckedAttr = $oItem->Get('state') == 1 ? 'checked' : '';
//        $sCheckedClass = $sCheckedAttr ? 'checklist-item-state-complete' : '';
//
//        // TODO: Пользователь имеет права на изменение?
//        // TODO: Это изменяемый чек-лист?
//        $sEditSection = '';
//        if ($bEditMode) {
//            $sTextInput = el('input.checklist-item-text-input',  array('type' => 'text', 'name' => 'itemText', 'value' =>$sItemText, 'maxlength' => 255));
//            $sButtons = el('div.checklist-item-actions', null, array(
//                el('a.checklist-btn quiet', array('data-item-action' => 'edit'), \Dict::S('Checklist:Button:Edit')),
//                el('a.checklist-btn quiet', array('data-item-action' => 'remove'), \Dict::S('Checklist:Button:Remove')),
//                el('a.checklist-btn quiet', array('data-item-action' => 'apply'), \Dict::S('Checklist:Button:Apply')),
//                el('a.checklist-btn quiet', array('data-item-action' => 'cancel'), \Dict::S('Checklist:Button:Cancel')),
//            ));
//            $sEditSection = $sTextInput . $sButtons;
//        }
//
//        $sHtml = el('div.checklist-item.'.$sCheckedClass, array('data-item-id' => $iItemId), array(
//            el('div.checklist-item-checkbox', null,
//                el('input', array('type' => 'checkbox', 'name' => 'itemState', $sCheckedAttr))
//            ),
//            el('div.checklist-item-text', null, $sItemText),
//            el('div.checklist-item-checked-at.quiet', null, $sItemCheckedAt),
//            $sEditSection
//        ));
//
//        return $sHtml;
//    }
}

class _Checklist extends DBObject implements \Knowitop\TemplateInterface
{
    public function FillFromTemplate(\ObjectTemplate $oTemplate)
    {

        // TODO: использовать ExecAction в DBObject для шаблонов
        // $aContext = $oObj->ToArgs('this'); // TODO: для замены плейсхолдеров в шаблонах
        // // Apply context ($this->...$)
        // $sText = MetaModel::ApplyParams($oTemplate->Get('body'), $aContext);

        $this->Set('title', $oTemplate->Get('title'));
        $aItemsText = explode("\n", $oTemplate->Get('items_text'));
        $this->DBWrite();
        foreach ($aItemsText as $sItemText) {
            $oItem = MetaModel::NewObject('ChecklistItem'); // new ChecklistItem();
            $oItem->Set('checklist_id', $this->GetKey());
            $oItem->Set( 'text', $sItemText);
            $oItem->DBWrite();
        }
    }

    public function IsEditAllowed() {
        $oHost = $this->GetHostObject();
        return UserRights::IsActionAllowed(get_class($oHost), UR_ACTION_MODIFY, DBObjectSet::FromObject($oHost)) === UR_ALLOWED_YES;
    }

    public function Render($bEditMode = false) {

        $iChecklistId = $this->GetKey();
        $sChecklistName = $this->GetName();
        $aChecklistItems = array();
        $oItemSet = $this->Get('items_list');
        while($oItem = $oItemSet->Fetch())
            array_push($aChecklistItems, el('li', null, self::RenderItem($oItem, $bEditMode && $this->IsEditAllowed())));

        $sChecklistActions = '';
        if ($bEditMode && $this->IsEditAllowed()) {
            $sChecklistActions = el('div.checklist-new-item', null, array(
                    el('a.checklist-btn.quiet', array('data-checklist-action' => 'new_item'), \Dict::S('Checklist:Button:NewItem')),
                    el('input.checklist-item-text-input', array('name' => 'newItemText', 'maxlength' => 255, 'placeholder' => \Dict::S('Checklist:NewItemPlaceholder'))),
                    el('a.checklist-btn.quiet', array('data-checklist-action' => 'remove_list'), \Dict::S('Checklist:Button:RemoveList')),
                    el('div.checklist-item-actions', null, array(
                            el('a.checklist-btn.quiet', array('data-checklist-action' => 'save_item'), \Dict::S('Checklist:Button:Apply')),
                            el('a.checklist-btn.quiet', array('data-checklist-action' => 'cancel_item'), \Dict::S('Checklist:Button:Cancel')))
                    )
                )
            );
        }

        $sHtml = el('div.checklist', array('data-checklist-id' => $iChecklistId, 'data-checklist-name' => $sChecklistName), array(
            el('h3.checklist-title', array('data-checklist-id'=> $iChecklistId), $sChecklistName),
            el('ol.checklist-items', null, $aChecklistItems),
            $sChecklistActions
        ));

        return $sHtml;
    }

    public static function RenderItem(\DBObject $oItem, $bEditMode = false)
    {
        // TODO: Пользователь имеет права на этот чеклист?
        $iItemId = $oItem->GetKey();
        $sItemText = $oItem->Get('text');
        $sItemCheckedAt = $oItem->GetAsHTML('checked_at');
        $sCheckedAttr = $oItem->Get('state') == 1 ? 'checked' : '';
        $sCheckedClass = $sCheckedAttr ? 'checklist-item-state-complete' : '';

        // TODO: Пользователь имеет права на изменение?
        // TODO: Это изменяемый чек-лист?
        $sEditSection = '';
        if ($bEditMode) {
            $sTextInput = el('input.checklist-item-text-input',  array('type' => 'text', 'name' => 'itemText', 'value' =>$sItemText, 'maxlength' => 255));
            $sButtons = el('div.checklist-item-actions', null, array(
                el('a.checklist-btn quiet', array('data-item-action' => 'edit'), \Dict::S('Checklist:Button:Edit')),
                el('a.checklist-btn quiet', array('data-item-action' => 'remove'), \Dict::S('Checklist:Button:Remove')),
                el('a.checklist-btn quiet', array('data-item-action' => 'apply'), \Dict::S('Checklist:Button:Apply')),
                el('a.checklist-btn quiet', array('data-item-action' => 'cancel'), \Dict::S('Checklist:Button:Cancel')),
            ));
            $sEditSection = $sTextInput . $sButtons;
        }

        $sHtml = el('div.checklist-item.'.$sCheckedClass, array('data-item-id' => $iItemId), array(
            el('div.checklist-item-checkbox', null,
                el('input', array('type' => 'checkbox', 'name' => 'itemState', $sCheckedAttr))
            ),
            el('div.checklist-item-text', null, $sItemText),
            el('div.checklist-item-checked-at.quiet', null, $sItemCheckedAt),
            $sEditSection
        ));

        return $sHtml;
    }

    protected function GetHostObject()
    {
        return MetaModel::GetObject($this->Get('obj_class'), $this->Get('obj_key'), true);
    }

    public function SetHostObject($oHost, $bUpdateOnChange = false)
    {
        $sClass = get_class($oHost);
        $this->Set('obj_class', $sClass);
        $this->Set('obj_key', $oHost->GetKey());

        $aCallSpec = array($sClass, 'MapContextParam');
        if (is_callable($aCallSpec))
        {
            $sAttCode = call_user_func($aCallSpec, 'org_id'); // Returns null when there is no mapping for this parameter
            if (MetaModel::IsValidAttCode($sClass, $sAttCode))
            {
                $iOrgId = $oHost->Get($sAttCode);
                if ($iOrgId > 0)
                {
                    if ($iOrgId != $this->Get('obj_org_id'))
                    {
                        $this->Set('obj_org_id', $iOrgId);
                        if ($bUpdateOnChange)
                        {
                            $this->DBUpdate();
                        }
                    }
                }
            }
        }
    }

    public function SetDefaultOrgId()
    {
        // First check that the organization CAN be fetched from the target class
        //
        $sClass = $this->Get('obj_class');
        $aCallSpec = array($sClass, 'MapContextParam');
        if (is_callable($aCallSpec))
        {
            $sAttCode = call_user_func($aCallSpec, 'org_id'); // Returns null when there is no mapping for this parameter
            if (MetaModel::IsValidAttCode($sClass, $sAttCode))
            {
                // Second: check that the organization CAN be fetched from the current user
                //
                if (MetaModel::IsValidClass('Person'))
                {
                    $aCallSpec = array($sClass, 'MapContextParam');
                    if (is_callable($aCallSpec))
                    {
                        $sAttCode = call_user_func($aCallSpec, 'org_id'); // Returns null when there is no mapping for this parameter
                        if (MetaModel::IsValidAttCode($sClass, $sAttCode))
                        {
                            // OK - try it
                            //
                            $oCurrentPerson = MetaModel::GetObject('Person', UserRights::GetContactId(), false);
                            if ($oCurrentPerson)
                            {
                                $this->Set('obj_org_id', $oCurrentPerson->Get($sAttCode));
                            }
                        }
                    }
                }
            }
        }
    }

    public static function MapContextParam($sContextParam)
    {
        if ($sContextParam == 'org_id')
        {
            return 'obj_org_id';
        }
        else
        {
            return null;
        }
    }

}

function el($tag, $attrs = null, $contents = null) {
    return \Knowitop\HtmlRenderer::render($tag, $attrs, $contents);
}
