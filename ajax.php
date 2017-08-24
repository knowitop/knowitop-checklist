<?php

require_once('../../approot.inc.php');
require_once(APPROOT.'/application/application.inc.php');
require_once(APPROOT.'/application/webpage.class.inc.php');
require_once(APPROOT.'/application/ajaxwebpage.class.inc.php');

function CheckUserRights($oObj)
{
    $sMsg = 'You have no rights for ' . get_class($oObj) . ':' . $oObj->GetKey();
    if (get_class($oObj) === 'Checklist') {
        if (!$oObj->IsEditAllowed()) throw new Exception($sMsg);
    } elseif (get_class($oObj) === 'ChecklistItem') {
        CheckUserRights(MetaModel::GetObject('Checklist', $oObj->Get('checklist_id'), true));
    } elseif (!ChecklistPlugin::IsHostObjEditAllowed($oObj)) {
        throw new Exception($sMsg);
    }
}

try
{
    require_once(APPROOT.'/application/startup.inc.php');

    require_once(APPROOT.'/application/loginwebpage.class.inc.php');
    LoginWebPage::DoLoginEx(null /* any portal */, false);

    $oPage = new ajax_page("");
    $oPage->no_cache();

    $sOperation = utils::ReadParam('operation', '');
    $aResult = array(
        'error' => false,
        'id' => null,
        'html' => '',
        'message' => 'ok'
    );

    switch($sOperation)
    {
        case 'remove_item':
            $iItemId = utils::ReadParam('id', '');
            $oItem = MetaModel::GetObject('ChecklistItem', $iItemId);
            CheckUserRights($oItem);
            if (!is_null($oItem)) $aResult['issues'] = $oItem->DBDelete()->GetIssues();
            echo json_encode($aResult);
            break;

        case 'edit_item':
            $iItemId = utils::ReadParam('id', '', false, 'integer');
            $sText = utils::ReadParam('text', '', false, 'string');
            $oItem = MetaModel::GetObject('ChecklistItem', $iItemId, true);
            CheckUserRights($oItem);
            $oItem->Set('text', $sText);
            $aResult['id'] = $oItem->DBWrite();
            echo json_encode($aResult);
            break;

        case 'check_item':
            $iItemId = utils::ReadParam('id', '', false, 'integer');
            $sState = utils::ReadParam('state', '', false, 'string');
            $oItem = MetaModel::GetObject('ChecklistItem', $iItemId, true);
            CheckUserRights($oItem);
            $oItem->Set('state', $sState === 'complete');
            $aResult['id'] = $oItem->DBWrite();
            $aResult['checked_at'] = $oItem->GetAsHtml('checked_at');
            echo json_encode($aResult);
            break;

        case 'add_item':
            $sChecklistId = utils::ReadParam('checklist_id', '', false, 'integer');
            $oChecklist = MetaModel::GetObject('Checklist', $sChecklistId, true); // Checklist must exists
            CheckUserRights($oChecklist);
            $sText = utils::ReadParam('text', '', false, 'string');
            $bEditMode = utils::ReadParam('edit_mode', '') === '1' || ChecklistPlugin::IsEditInPlaceAllowed();
            $oItem = MetaModel::NewObject('ChecklistItem');
            $oItem->Set('checklist_id', $oChecklist->GetKey());
            $oItem->Set('text', $sText);
            $aResult['id'] = $oItem->DBWrite();
            $aResult['html'] = $oChecklist->RenderItem($oItem, $bEditMode);
            echo json_encode($aResult);
            break;

        case 'edit_list':
            $sChecklistId = utils::ReadParam('checklist_id', '', false, 'integer');
            $oChecklist = MetaModel::GetObject('Checklist', $sChecklistId);
            CheckUserRights($oChecklist);
            $sTitle = utils::ReadParam('title', '', false, 'string');
            $oChecklist->Set('title', $sTitle);
            $aResult['id'] = $oChecklist->DBWrite();
            echo json_encode($aResult);
            break;

        case 'remove_list':
            $sChecklistId = utils::ReadParam('checklist_id', '', false, 'integer');
            $oChecklist = MetaModel::GetObject('Checklist', $sChecklistId);
            CheckUserRights($oChecklist);
            if (!is_null($oChecklist)) $aResult['issues'] = $oChecklist->DBDelete()->GetIssues(); // quiet if the list already removed
            echo json_encode($aResult);
            break;

        case 'create_list':
            $sHostId = utils::ReadParam('host_id', '', false, 'integer');
            $sHostClass = utils::ReadParam('host_class', '', false, 'class');
            $oHostObj = MetaModel::GetObject($sHostClass, $sHostId, true); // Host object must exists
            CheckUserRights($oHostObj);
            $bEditMode = utils::ReadParam('edit_mode', '') === '1'  || ChecklistPlugin::IsEditInPlaceAllowed();
            $oChecklist = MetaModel::NewObject('Checklist');
            $oChecklist->Set('title', Dict::S('Checklist:NewChecklistTitle'));
            $oChecklist->SetHostObject($oHostObj);
            $oChecklist->DBWrite();
            $aResult['id'] = $oChecklist->GetKey();
            $aResult['html'] .= $oChecklist->Render($bEditMode);
            echo json_encode($aResult);
            break;

        case 'create_list_from_template':
            $aSelected = utils::ReadParam('selected', '');
            $sHostId = utils::ReadParam('host_id', '', false, 'integer');
            $sHostClass = utils::ReadParam('host_class', '', false, 'class');
            $oHostObj = MetaModel::GetObject($sHostClass, $sHostId, true); // Host object must exists
            CheckUserRights($oHostObj);
            $bEditMode = utils::ReadParam('edit_mode', '') === '1';
            foreach($aSelected as $iId)
            {
                $oTemplate = MetaModel::GetObject('ChecklistTemplate', $iId, false);
                if ($oTemplate !== null)
                {
                    $oChecklist = MetaModel::NewObject('Checklist');
                    $oChecklist->SetHostObject($oHostObj);
                    $oChecklist->FillFromTemplate($oTemplate); // method from TemplateInterface
                    // $oChecklist->DBWrite(); // Write in FillFromTemplate
                    $aResult['id'] = $oChecklist->GetKey();
                    $aResult['html'] .= $oChecklist->Render($bEditMode || ChecklistPlugin::IsEditInPlaceAllowed());
                }
            }
            echo json_encode($aResult);
            break;

        // TODO: убрать в отдельный файл ajax.render?
        case 'select_template':
            $sHTML = '<div class="wizContainer" style="vertical-align:top;"><div>';

            $oFilter = new DBObjectSearch('ChecklistTemplate');
            $oSet = new CMDBObjectSet($oFilter);
            $oBlock = new DisplayBlock($oFilter, 'search', false);
            $sHTML .= $oBlock->GetDisplay($oPage, 'template_select', array('open' => false, 'currentId' => 'template_select'));
            $sHTML .= "<form id=\"fr_template_select\" OnSubmit=\"return TemplateDoSelect();\">\n";
            $sHTML .= "<div id=\"dr_template_select\" style=\"vertical-align:top;background: #fff;height:100%;overflow:auto;padding:0;border:0;\">\n";
            $sHTML .= "<div style=\"background: #fff; border:0; text-align:center; vertical-align:middle;\"><p>".Dict::S('UI:Message:EmptyList:UseSearchForm')."</p></div>\n";
            $sHTML .= "</div>\n";
            $sHTML .= "<input type=\"button\" id=\"btn_cancel_template_select\" value=\"".Dict::S('UI:Button:Cancel')."\" onClick=\"$('#template_dlg').dialog('close');\">&nbsp;&nbsp;";
            $sHTML .= "<input type=\"button\" id=\"btn_ok_template_select\" value=\"".Dict::S('UI:Button:Ok')."\" onClick=\"TemplateDoSelect();\">";
            $sHTML .= "<input type=\"hidden\" id=\"count_template_select\" value=\"0\">";
            $sHTML .= "</form>\n";
            $sHTML .= '</div></div>';

            $oPage->add($sHTML);
            $oPage->add_ready_script("$('#fs_template_select').bind('submit', function() { TemplateDoSearch(); return false;} );\n");
            break;

        case 'search_template':
            $oFilter = new DBObjectSearch('ChecklistTemplate');
            $oBlock = new DisplayBlock($oFilter, 'list', false);
            $oBlock->Display($oPage, 'template_select_results', array('cssCount'=> '#count_template_select', 'menu' => false, 'selection_mode' => true, 'selection_type' => 'single')); // Don't display the 'Actions' menu on the results
            break;

        default:
            $oPage->p("Missing argument 'operation'");
    }
    $oPage->output();
}
catch (Exception $e)
{
    // note: transform to cope with XSS attacks
    $aResult['error'] = true;
    $aResult['message'] = htmlentities($e->GetMessage(), ENT_QUOTES, 'utf-8');
    echo json_encode($aResult);

    IssueLog::Error($e->getMessage());
}
