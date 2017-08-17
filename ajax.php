<?php

require_once('../../approot.inc.php');
require_once(APPROOT.'/application/application.inc.php');
require_once(APPROOT.'/application/webpage.class.inc.php');
require_once(APPROOT.'/application/ajaxwebpage.class.inc.php');

// require_once(APPROOT.'/application/wizardhelper.class.inc.php');


try
{
    require_once(APPROOT.'/application/startup.inc.php');
    //	require_once(APPROOT.'/application/user.preferences.class.inc.php');

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
            if (!is_null($oItem)) $aResult['issues'] = $oItem->DBDelete()->GetIssues();
            echo json_encode($aResult);
            break;

        case 'add_item':
            $sChecklistId = utils::ReadParam('checklist_id', '', false, 'integer');
            $oChecklist = MetaModel::GetObject('Checklist', $sChecklistId);
            $sText = utils::ReadParam('text', '', false, 'string');
            $oItem = MetaModel::NewObject('ChecklistItem');
            $oItem->Set('checklist_id', $sChecklistId);
            $oItem->Set('text', $sText);
            $aResult['id'] = $oItem->DBWrite();
            $aResult['html'] = $oChecklist->RenderItem($oItem);
            echo json_encode($aResult);
            break;

        case 'edit_item':
            $iItemId = utils::ReadParam('id', '', false, 'integer');
            $sText = utils::ReadParam('text', '', false, 'string');
            $oItem = MetaModel::GetObject('ChecklistItem', $iItemId, true);
            $oItem->Set('text', $sText);
            $aResult['id'] = $oItem->DBWrite();
            echo json_encode($aResult);
            break;

        case 'check_item':
            $iItemId = utils::ReadParam('id', '', false, 'integer');
            $sState = utils::ReadParam('state', '', false, 'string');
            $oItem = MetaModel::GetObject('ChecklistItem', $iItemId, true);
            $oItem->Set('state', $sState === 'complete');
            $aResult['id'] = $oItem->DBWrite();
            $aResult['checked_at'] = $oItem->GetAsHtml('checked_at');
//            $d = new AttributeDateTime('x', []);
//            AttributeDateTime::
            echo json_encode($aResult);
            break;

        case 'create_list':
            $sHostId = utils::ReadParam('host_id', '', false, 'integer');
            $sHostClass = utils::ReadParam('host_class', '', false, 'class');
            $bEditMode = utils::ReadParam('edit_mode', '') === '1';
            $oChecklist = MetaModel::NewObject('Checklist');
            $oChecklist->Set('title', Dict::S('Checklist:NewChecklistTitle'));
            $oChecklist->Set('obj_key', $sHostId);
            $oChecklist->Set('obj_class', $sHostClass);
            $oChecklist->DBWrite();
            $aResult['id'] = $oChecklist->GetKey();
            $aResult['html'] .= $oChecklist->Render($bEditMode);
            echo json_encode($aResult);
            break;

        case 'remove_list':
            $iListId = utils::ReadParam('id', '');
            $oChecklist = MetaModel::GetObject('Checklist', $iListId);
            if (!is_null($oChecklist)) $aResult['issues'] = $oChecklist->DBDelete()->GetIssues();
            echo json_encode($aResult);
            break;

        // TODO: убрать в отдельный файл ajax.render
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

        case 'create_list_from_template':
            $aSelected = utils::ReadParam('selected', '');
            $sHostId = utils::ReadParam('host_id', '', false, 'integer');
            $sHostClass = utils::ReadParam('host_class', '', false, 'class');
            $bEditMode = utils::ReadParam('edit_mode', '') === '1';

            $oHostObj = MetaModel::GetObject($sHostClass, $sHostId, true);
            //$aContext = $oHostObj->ToArgs('this'); // TODO: для замены плейсхолдеров в шаблонах

            // if (!empty($sJson))
            // {
            //     $oWizardHelper = WizardHelper::FromJSON($sJson);
            //     $oObj = $oWizardHelper->GetTargetObject();
            //     $aContext = $oObj->ToArgs('this'); // TODO: для замены плейсхолдеров в шаблонах
            // }
            // else
            // {
            //     // Bug!!!!
            //     $aContext = array();
            // }

            foreach($aSelected as $iId)
            {
                $oTemplate = MetaModel::GetObject('ChecklistTemplate', $iId, false);
                if ($oTemplate !== null)
                {
                    //$oChecklist = $oTemplate->CreateTargetObject(array('item_object' => $oHostObj));
                    $oChecklist = MetaModel::NewObject('Checklist');
                    $oChecklist->Set('obj_key', $oHostObj->GetKey());
                    $oChecklist->Set('obj_class', $oHostObj->Get('finalclass'));
                    $oChecklist->FillFromTemplate($oTemplate); // method from TemplateInterface
                    $oChecklist->DBWrite();
                    $aResult['id'] = $oChecklist->GetKey();
                    $aResult['html'] .= $oChecklist->Render($bEditMode);
//                    $aResult['html'] .= ChecklistPlugin::RenderChecklist($oChecklist, $bEditMode);
                }
            }
            echo json_encode($aResult);
            // $oPage->add(json_encode($aResult));
            break;


        // case 'create_from_template':
        //     $oPage->SetContentType('text/html');
        //     $iTemplateId = utils::ReadParam('template_id', '', false, 'integer');
        //     $aParams = utils::ReadParam('param', array(), false, 'raw_data');
        //     var_dump($aParams);
        //     $oTemplate = MetaModel::GetObject('Template', $iTemplateId);
        //     $oObj = $oTemplate->CreateTargetObject($aParams);
        //     cmdbAbstractObject::DisplayCreationForm($oPage, get_class($oObj), $oObj, array(), array('action' => utils::GetAbsoluteUrlAppRoot().'pages/UI.php'));
        //     break;

//
//        case 'create_ssk_report':
//            $sWOId = utils::ReadParam('workorder_id', '', false, 'integer');
//
//            // TODO: параметр в конфиг
//            $sUrl = MetaModel::GetModuleSetting('knowitop-reports', 'url', 'http://192.168.0.230:3000/ssk/report');
//
//            $oWorkOrder = MetaModel::GetObject('WorkOrder', $sWOId, true);
//            $aWorkOrder = array();
//            if (!is_null($oWorkOrder)) {
//                foreach (MetaModel::GetAttributesList(get_class($oWorkOrder)) as $sAttCode) {
//                    $aWorkOrder[$sAttCode] = $oWorkOrder->Get($sAttCode);
//                }
//            }
//
//            $oRequest = MetaModel::GetObject('Ticket', $oWorkOrder->Get('ticket_id'), true);
//            $aRequest = array();
//            if (!is_null($oRequest)) {
//                foreach (MetaModel::GetAttributesList(get_class($oRequest)) as $sAttCode) {
//                    $aRequest[$sAttCode] = $oRequest->Get($sAttCode);
//                }
//            }
//
//            $oCISet = DBObjectSet::FromLinkSet($oWorkOrder, 'functionalcis_list', 'functionalci_id');
//            $aCI = array();
//            while ($oCI = $oCISet->Fetch()) {
//                foreach (MetaModel::GetAttributesList(get_class($oCI)) as $sAttCode) {
//                    $aCI[get_class($oCI)][$sAttCode] = $oCI->Get($sAttCode);
//                }
//            }
//
//            $oChecklist = MetaModel::GetObjectFromOQL('SELECT Checklist WHERE obj_key = ' . $oWorkOrder->GetKey());
//            $aChecklist = array();
//            if (!is_null($oChecklist)) {
//                $oItemSet = $oChecklist->Get('items_list');
//                while ($oItem = $oItemSet->Fetch()) {
//                    $sItemNum = 'p' . intval($oItem->Get('text'));
//                    $aChecklist[$sItemNum] = $oItem->Get('state') ? 'Норма' : 'Нет';
//                }
//            }
//
//            $aData = array(
//                'ssk' => $aCI['Frame'],
//                'vcs_controller' => $aCI['NetworkDevice'],
//                'request' => $aRequest,
//                'workorder' => $aWorkOrder,
//                'checklist' => $aChecklist
//                // 'checklists' => array('vcs_controller' => $aChecklist)
//            );
//
//            // $sData = http_build_query($aData);
//            $sData = json_encode($aData);
//            $aParams = array('http' => array(
//                'method' => 'POST',
//                'content' => $sData,
//                'header'=> "Content-type: application/json\r\nContent-Length: ".strlen($sData)."\r\n",
//            ));
//            $ctx = stream_context_create($aParams);
//            $fp = @fopen($sUrl, 'rb', false, $ctx);
//            if (!$fp)
//            {
//                global $php_errormsg;
//                if (isset($php_errormsg))
//                {
//                    throw new Exception("Wrong URL: $sUrl, $php_errormsg");
//                }
//                elseif ((strtolower(substr($sUrl, 0, 5)) == 'https') && !extension_loaded('openssl'))
//                {
//                    throw new Exception("Cannot connect to $sUrl: missing module 'openssl'");
//                }
//                else
//                {
//                    throw new Exception("Wrong URL: $sUrl");
//                }
//            }
//            $response = @stream_get_contents($fp);
//            if ($response === false)
//            {
//                throw new Exception("Problem reading data from $sUrl, $php_errormsg");
//            }
//            $aResponseHeaders = array();
//            $aMeta = stream_get_meta_data($fp);
//            $aHeaders = $aMeta['wrapper_data'];
//            foreach($aHeaders as $sHeaderString)
//            {
//                if(preg_match('/^([^:]+): (.+)$/', $sHeaderString, $aMatches))
//                {
//                    $aResponseHeaders[$aMatches[1]] = trim($aMatches[2]);
//                }
//            }
//
//            $oPage = new ajax_page('');
//            $oPage->SetContentType($aResponseHeaders['Content-Type']);
//            $oPage->SetContentDisposition('attachment', 'report_ssk.xlsx');
//            $oPage->add($response);
//            $oPage->TrashUnexpectedOutput();
//            break;

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
