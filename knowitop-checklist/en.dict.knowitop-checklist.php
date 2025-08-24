<?php
/**
 * Localized data
 *
 * @copyright   Copyright (C) 2019-2022 Vladimir Kunin https://knowitop.ru
 */

Dict::Add('EN US', 'English', 'English', array(
	'Class:Checklist' => 'Checklist',
	'Class:Checklist+' => 'Checklist',
	'Class:Checklist/Attribute:obj_class' => 'Object class',
	'Class:Checklist/Attribute:obj_class+' => '',
	'Class:Checklist/Attribute:obj_key' => 'Object Id',
	'Class:Checklist/Attribute:obj_key+' => '',
	'Class:Checklist/Attribute:obj_org_id' => 'Organization',
	'Class:Checklist/Attribute:obj_org_id+' => '',
	'Class:Checklist/Attribute:title' => 'Title',
	'Class:Checklist/Attribute:title+' => '',
	'Class:Checklist/Attribute:items_list' => 'Items',
	'Class:Checklist/Attribute:items_list+' => 'Checklist items',
	'Class:Checklist/Attribute:template_key' => 'Template',
	'Class:Checklist/Attribute:template_key+' => 'Template key',

	'Class:ChecklistItem' => 'Checklist Item',
	'Class:ChecklistItem+' => 'Checklist Item',
	'Class:ChecklistItem/Attribute:checklist_id' => 'Checklist',
	'Class:ChecklistItem/Attribute:checklist_id+' => '',
	'Class:ChecklistItem/Attribute:checklist_title' => 'Checklist',
	'Class:ChecklistItem/Attribute:checklist_title+' => '',
	'Class:ChecklistItem/Attribute:checklist_org_id' => 'Organization',
	'Class:ChecklistItem/Attribute:checklist_org_id+' => '',
	'Class:ChecklistItem/Attribute:text' => 'Text',
	'Class:ChecklistItem/Attribute:text+' => '',
	'Class:ChecklistItem/Attribute:state' => 'State',
	'Class:ChecklistItem/Attribute:state+' => '',
	'Class:ChecklistItem/Attribute:created_at' => 'Created at',
	'Class:ChecklistItem/Attribute:created_at+' => '',
	'Class:ChecklistItem/Attribute:checked_at' => 'Checked at',
	'Class:ChecklistItem/Attribute:checked_at+' => '',
	'Class:ChecklistItem/Attribute:checked_by' => 'Checked by',
	'Class:ChecklistItem/Attribute:checked_by+' => '',
	'Class:ChecklistItem/Attribute:checklist_id_friendlyname' => 'Checklist',
	'Class:ChecklistItem/Attribute:checklist_id_friendlyname+' => '',

	'Class:ChecklistTemplate' => 'Checklist Template',
	'Class:ChecklistTemplate+' => 'Checklist Template',
	'Class:ChecklistTemplate/Name' => '%1$s',
	'Class:ChecklistTemplate/Attribute:checklist_title' => 'Checklist title',
	'Class:ChecklistTemplate/Attribute:checklist_title+' => '',
	'Class:ChecklistTemplate/Attribute:checklist_items' => 'Checklist items',
	'Class:ChecklistTemplate/Attribute:checklist_items+' => 'Checklist items, one for each row',

	'UI:ChecklistTemplate:HelpBlockLabel' => 'Help',
	'UI:ChecklistTemplate:HelpBlockContent' => '<p>In the template data, you can use the placeholders <code>$context->att_code$</code> and <code>$template->att_code$</code> to substitute values from the context object and the template itself into the fields of the created checklist.<p>
	<p>For example, if you specify the checklist title in the template as <b>Checklist for $context->ref$</b>, when creating a checklist using this template for a user request R-012345, it will be named <b>Checklist for R-012345</b>. Substitution works similarly for checklist items.</p>
	<p>You can also substitute values from objects associated with the context object. For example, <code>$context->caller_id->phone$</code> will be replaced with the phone number of the ticket caller.</p>',

	'Checklist:Button:NewItem' => 'Add an item...',
	'Checklist:NewItemPlaceholder' => 'Add an item...',
	'Checklist:NewChecklistTitle' => 'Checklist',
	'Checklist:Button:Edit' => 'Edit',
	'Checklist:Button:Delete' => 'Delete...',
	'Checklist:Button:Save' => 'Save',
	'Checklist:Button:Cancel' => 'Cancel',
	'Checklist:Button:NewList' => 'Add Checklist',
	'Checklist:Button:NewListFromTmpl' => 'Copy from template...',
	'Checklist:TabTitle_Count' => 'Checklists (%1$s)',
	'Checklist:EmptyTabTitle' => 'Checklists',
	'Checklist:TabTitle+' => 'Related checklists',

	'UI:Checklist:DlgPickATemplate' => 'Select template',
	'UI:Checklist:DeleteDlg:Title' => 'Delete Checklist?',
	'UI:Checklist:DeleteDlg:Msg' => 'This checklist will be permanently deleted. There is no way to get it back.',
	'UI:Checklist:DeleteDlg:Delete' => 'Delete Checklist',
	'UI:Checklist:DeleteDlg:Cancel' => 'Cancel',
	'UI:ChecklistItem:DeleteDlg:Title' => 'Delete item?',
	'UI:ChecklistItem:DeleteDlg:Msg' => 'This item will be permanently deleted.',
	'UI:ChecklistItem:DeleteDlg:Delete' => 'Delete item',
	'UI:ChecklistItem:DeleteDlg:Cancel' => 'Cancel',

	'Class:WorkOrderTemplate/Attribute:checklist_templates_list' => 'Checklist templates',
	'Class:WorkOrderTemplate/Attribute:checklist_templates_list+' => 'All related checklist templates',

	'Class:lnkChecklistTemplateToWorkOrderTemplate' => 'Link Checklist Template / Work Order Template',
	'Class:lnkChecklistTemplateToWorkOrderTemplate+' => '',
	'Class:lnkChecklistTemplateToWorkOrderTemplate/Attribute:checklist_template_id' => 'Checklist template',
	'Class:lnkChecklistTemplateToWorkOrderTemplate/Attribute:checklist_template_id+' => '',
	'Class:lnkChecklistTemplateToWorkOrderTemplate/Attribute:workorder_template_id' => 'Work order template',
	'Class:lnkChecklistTemplateToWorkOrderTemplate/Attribute:workorder_template_id+' => '',
	'Class:lnkChecklistTemplateToWorkOrderTemplate/Attribute:checklist_template_id_friendlyname' => 'Checklist Template',
	'Class:lnkChecklistTemplateToWorkOrderTemplate/Attribute:checklist_template_id_friendlyname+' => '',
	'Class:lnkChecklistTemplateToWorkOrderTemplate/Attribute:workorder_template_id_friendlyname' => 'Work order template',
	'Class:lnkChecklistTemplateToWorkOrderTemplate/Attribute:workorder_template_id_friendlyname+' => '',
));