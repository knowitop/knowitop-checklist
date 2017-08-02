<?php
/**
 * Checklist plugin
 *
 * @copyright   Copyright (C) 2017 Vladimir Kunin <v.b.kunin@gmail.com>
 */

//namespace Knowitop;

class ChecklistPlugin implements \iApplicationUIExtension
{

    // protected static $m_bIsModified = false;
    const MODULE_NAME = 'knowitop-checklist';

    public function OnDisplayProperties($oObject, \WebPage $oPage, $bEditMode = false)
    {

    }

    public function OnDisplayRelations($oObject, \WebPage $oPage, $bEditMode = false)
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
        // Possible return values are:
        // HILIGHT_CLASS_CRITICAL, HILIGHT_CLASS_WARNING, HILIGHT_CLASS_OK, HILIGHT_CLASS_NONE
        return HILIGHT_CLASS_NONE;
    }

    public function EnumAllowedActions(\DBObjectSet $oSet)
    {
        // No action
        return array();
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

    public function DisplayChecklists(\DBObject $oObject, \iTopWebPage $oPage, $bEditMode = false)
    {

        $bEditMode = self::IsEditInPlace() || $bEditMode;

        $sApiUrl = \utils::GetAbsoluteUrlModulesRoot().self::MODULE_NAME.'/ajax.php';
        $iHostObjId = $oObject->GetKey();
        $sHostObjClass = get_class($oObject);
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
        $oSearch = \DBObjectSearch::FromOQL("SELECT Checklist WHERE obj_class = :class AND obj_key = :obj_key");
        $oSet = new \DBObjectSet($oSearch, array(), array('class' => get_class($oObject), 'obj_key' => $oObject->GetKey()));

        //TODO: сделать выбор: отображать кол-во чек-листов или сумму элементов на вклдаке?
        $sTabLabel = ($oSet->Count() > 0) ? \Dict::Format('Checklist:TabTitle_Count', $oSet->Count()) : \Dict::S('Checklist:EmptyTabTitle');
        $oPage->SetCurrentTab($sTabLabel);

        // todo: translate
        $oPage->p(\MetaModel::GetClassIcon('Checklist').'&nbsp;'.\Dict::S('Checklist:TabTitle+'));

        $oPage->add_linked_stylesheet(\utils::GetAbsoluteUrlModulesRoot().self::MODULE_NAME.'/css/checklist.css');
        // TODO: разбить скрипт на 2: для редактировани и использования чек-листов
        $oPage->add_linked_script("../js/wizardhelper.js");
        $oPage->add_linked_script(\utils::GetAbsoluteUrlModulesRoot().self::MODULE_NAME.'/js/checklist.js');

        while($oChecklist = $oSet->Fetch())
        {
            $oPage->add(self::RenderChecklist($oChecklist, $bEditMode));
            // TODO: переделать в метод объекта?
            // $oPage->add($oChecklist->Render($bEditMode));
        }

        if ($bEditMode) {
            // $sNewListDiv = "<div class='checklist-new-list'>";
            // $sNewListDiv .= "<a class=\"checklist-btn\" data-checklist-action='create_list'>".Dict::S('Checklist:Button:NewList')."</a>";
            // $sNewListDiv .= "<a class=\"checklist-btn\" data-checklist-action='create_list_from_template'>".Dict::S('Checklist:Button:NewListFromTmpl')."</a>";
            // $sNewListDiv .= "</div>";
            $sNewListDiv = el('div.checklist-new-list', null, array(
                el('a.checklist-btn', array('data-checklist-action' => 'create_list'), \Dict::S('Checklist:Button:NewList')),
                el('a.checklist-btn', array('data-checklist-action' => 'create_list_from_template'), \Dict::S('Checklist:Button:NewListFromTmpl')),
            ));
            $oPage->add($sNewListDiv);
        }
    }

    // public static function RenderChecklist(DBObject $oChecklist, $bEditMode = false) {
    //
    //     $bEditMode = self::IsEditInPlace() || $bEditMode;
    //
    //     $iChecklistId = $oChecklist->GetKey();
    //     $sChecklistName = $oChecklist->GetName();
    //     $sHtml = "<div class=\"checklist\" data-checklist-id=\"$iChecklistId\" data-checklist-name='$sChecklistName'>";
    //     $sHtml .= "<h3 class='checklist-title'>$sChecklistName</h3>";
    //
    //     $sHtml .= "<ol class='checklist-items'>";
    //     $oItemSet = $oChecklist->Get('items_list');
    //     while($oItem = $oItemSet->Fetch()) $sHtml .= "<li>".self::RenderChecklistItem($oItem, $bEditMode)."</li>";
    //     $sHtml .= "</ol>";
    //
    //     if ($bEditMode) {
    //         $sHtml .= "<div class='checklist-new-item'>";
    //         $sHtml .= "<a class=\"checklist-btn quiet\" data-checklist-action='new_item'>".Dict::S('Checklist:Button:NewItem')."</a>";
    //         $sHtml .= "<input type='text' name='newItemText' class='checklist-item-text-input' placeholder='".Dict::S('Checklist:NewItemPlaceholder')."' maxlength='255'/>";
    //         // TODO: куда деть кнопку удаления?
    //         $sHtml .= "<a class=\"checklist-btn quiet\" data-checklist-action='remove_list'>".Dict::S('Checklist:Button:RemoveList')."</a>";
    //         $sHtml .= "<div class=\"checklist-item-actions\">";
    //         $sHtml .= "<a class=\"checklist-btn quiet\" data-checklist-action='save_item'>".Dict::S('Checklist:Button:Apply')."</a>";
    //         $sHtml .= "<a class=\"checklist-btn quiet\" data-checklist-action='cancel_item'>".Dict::S('Checklist:Button:Cancel')."</a>";
    //         $sHtml .= "</div>"; // checklist-item-actions
    //         $sHtml .= "</div>"; // checklist-new-item
    //     }
    //
    //     $sHtml .= "</div>"; // checklist
    //
    //     return $sHtml;
    // }

    // public static function RenderChecklistItem(DBObject $oItem, $bEditMode = false)
    // {
    //     // TODO: Пользователь имеет права на этот чеклист?
    //     $iItemId = $oItem->GetKey();
    //     $sItemText = $oItem->Get('text');
    //     $sCheckedAttr = $oItem->Get('state') == 1 ? 'checked' : '';
    //     $sCheckedClass = $sCheckedAttr ? 'checklist-item-state-complete' : '';
    //
    //     $sItem = "<div class=\"checklist-item $sCheckedClass\" data-item-id=\"$iItemId\">";
    //     $sItem .= "<div class=\"checklist-item-checkbox\"><input type=\"checkbox\" name=\"itemState\" $sCheckedAttr/></div>";
    //     $sItem .= "<div class=\"checklist-item-text\">$sItemText</div>";
    //
    //     // TODO: Пользователь имеет права на изменение?
    //     if ($bEditMode) {
    //         $sItem .= "<input type=\"text\" name=\"itemText\" class=\"checklist-item-text-input\" value=\"$sItemText\" maxlength='255'/>";
    //         $sButtons = "<div class=\"checklist-item-actions\">";
    //         $sButtons .= "<a class=\"checklist-btn quiet\" data-item-action='edit'>".Dict::S('Checklist:Button:Edit')."</a>";
    //         $sButtons .= "<a class=\"checklist-btn quiet\" data-item-action='remove'>".Dict::S('Checklist:Button:Remove')."</a>";
    //         $sButtons .= "<a class=\"checklist-btn quiet\" data-item-action='apply'>".Dict::S('Checklist:Button:Apply')."</a>";
    //         $sButtons .= "<a class=\"checklist-btn quiet\" data-item-action='cancel'>".Dict::S('Checklist:Button:Cancel')."</a>";
    //         $sButtons .= "</div>";
    //
    //         $sItem .= $sButtons;
    //     }
    //     $sItem .= "</div>";
    //     return $sItem;
    // }

    public static function RenderChecklist(\DBObject $oChecklist, $bEditMode = false) {

        $bEditMode = self::IsEditInPlace() || $bEditMode;

        $iChecklistId = $oChecklist->GetKey();
        $sChecklistName = $oChecklist->GetName();
        $aChecklistItems = array();
        $oItemSet = $oChecklist->Get('items_list');
        while($oItem = $oItemSet->Fetch())
            array_push($aChecklistItems, el('li', null, self::RenderChecklistItem($oItem, $bEditMode)));

        $sChecklistActions = !$bEditMode ? '' : el('div.checklist-new-item', null, array(
                el('a.checklist-btn.quiet', array('data-checklist-action' => 'new_item'), \Dict::S('Checklist:Button:NewItem')),
                el('input.checklist-item-text-input', array('name' => 'newItemText', 'maxlength' => 255, 'placeholder' => \Dict::S('Checklist:NewItemPlaceholder'))),
                el('a.checklist-btn.quiet', array('data-checklist-action' => 'remove_list'), \Dict::S('Checklist:Button:RemoveList')),
                el('div.checklist-item-actions', null, array(
                        el('a.checklist-btn.quiet', array('data-checklist-action' => 'save_item'), \Dict::S('Checklist:Button:Apply')),
                        el('a.checklist-btn.quiet', array('data-checklist-action' => 'cancel_item'), \Dict::S('Checklist:Button:Cancel')))
                )
            ));

        $sHtml = el('div.checklist', array('data-checklist-id' => $iChecklistId, 'data-checklist-name' => $sChecklistName), array(
            el('h3.checklist-title', array('data-checklist-id'=> $iChecklistId), $sChecklistName),
            el('ol.checklist-items', null, $aChecklistItems),
            $sChecklistActions
        ));

        return $sHtml;
    }

    public static function RenderChecklistItem(\DBObject $oItem, $bEditMode = false)
    {
        // TODO: Пользователь имеет права на этот чеклист?
        $iItemId = $oItem->GetKey();
        $sItemText = $oItem->Get('text');
        $sItemCheckedAt = $oItem->GetAsHTML('checked_at');
        $sCheckedAttr = $oItem->Get('state') == 1 ? 'checked' : '';
        $sCheckedClass = $sCheckedAttr ? 'checklist-item-state-complete' : '';

        $sItem = "<div class=\"checklist-item $sCheckedClass\" data-item-id=\"$iItemId\">";
        $sItem .= "<div class=\"checklist-item-checkbox\"><input type=\"checkbox\" name=\"itemState\" $sCheckedAttr/></div>";
        $sItem .= "<div class=\"checklist-item-text\">$sItemText</div>";
        $sItem .= "<div class=\"checklist-item-checked-at quiet\">$sItemCheckedAt</div>";

        // TODO: Пользователь имеет права на изменение?
        if ($bEditMode) {
            $sItem .= "<input type=\"text\" name=\"itemText\" class=\"checklist-item-text-input\" value=\"$sItemText\" maxlength='255'/>";
            $sButtons = "<div class=\"checklist-item-actions\">";
            $sButtons .= "<a class=\"checklist-btn quiet\" data-item-action='edit'>".\Dict::S('Checklist:Button:Edit')."</a>";
            $sButtons .= "<a class=\"checklist-btn quiet\" data-item-action='remove'>".\Dict::S('Checklist:Button:Remove')."</a>";
            $sButtons .= "<a class=\"checklist-btn quiet\" data-item-action='apply'>".\Dict::S('Checklist:Button:Apply')."</a>";
            $sButtons .= "<a class=\"checklist-btn quiet\" data-item-action='cancel'>".\Dict::S('Checklist:Button:Cancel')."</a>";
            $sButtons .= "</div>";

            $sItem .= $sButtons;
        }
        $sItem .= "</div>";

        return $sItem;
    }

}

function el($tag, $attrs = null, $contents = null) {
    return \Knowitop\HtmlRenderer::render($tag, $attrs, $contents);
}

class _ChecklistTemplate extends \ObjectTemplate implements \Knowitop\iObjectTemplate
{

    static $sTargetClass = 'Checklist';

    public function GetTargetClass() {
        return self::$sTargetClass;
    }

    public function CreateTargetObject($aParams = array()) {
        // $aParams['obj_key'];
        // $aParams['obj_class'];
        // $aParams['item_object'];

        // TODO: использовать ExecAction в DBObject для шаблонов
        // $aContext = $oObj->ToArgs('this'); // TODO: для замены плейсхолдеров в шаблонах
        // // Apply context ($this->...$)
        // $sText = MetaModel::ApplyParams($oTemplate->Get('body'), $aContext);

        $iParentId = isset($aParams['item_object']) ? $aParams['item_object']->GetKey() : $aParams['obj_key'];
        $sParentClass = isset($aParams['item_object']) ? get_class($aParams['item_object']) : $aParams['obj_class'];

        $oNewChecklist = MetaModel::NewObject($this->GetTargetClass());
        //$oNewChecklist = new Checklist();
        $oNewChecklist->Set('obj_key', $iParentId);
        $oNewChecklist->Set('obj_class', $sParentClass);
        $oNewChecklist->Set('title', $this->Get('title'));
        $oNewChecklist->DBWrite();

        $aItemsText = explode("\n", $this->Get('items_text'));
        foreach ($aItemsText as $sItemText) {
            $oItem = MetaModel::NewObject('ChecklistItem'); // new ChecklistItem();
            $oItem->Set('checklist_id', $oNewChecklist->GetKey());
            $oItem->Set( 'text', $sItemText);
            $oItem->DBWrite();
        }

        return $oNewChecklist;

    }

}