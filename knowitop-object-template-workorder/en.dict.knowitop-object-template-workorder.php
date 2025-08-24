<?php
/**
 * Localized data
 *
 * @copyright   Copyright (C) 2019-2022 Vladimir Kunin https://knowitop.ru
 */

//
// Class: WorkOrderTemplate
//

Dict::Add('EN US', 'English', 'English', array(
	'Class:WorkOrderTemplate' => 'Work Order Template',
	'Class:WorkOrderTemplate+' => '',
	'Class:WorkOrderTemplate/Attribute:wo_name' => 'Work order name',
	'Class:WorkOrderTemplate/Attribute:wo_name+' => '',
	'Class:WorkOrderTemplate/Attribute:wo_description' => 'Work order description',
	'Class:WorkOrderTemplate/Attribute:wo_description+' => '',
	'Class:WorkOrderTemplate/Attribute:wo_team_id' => 'Team',
	'Class:WorkOrderTemplate/Attribute:wo_team_id+' => '',
	'Class:WorkOrderTemplate/Attribute:wo_team_name' => 'Team',
	'Class:WorkOrderTemplate/Attribute:wo_team_name+' => '',
	'Class:WorkOrderTemplate/Attribute:wo_agent_id' => 'Agent',
	'Class:WorkOrderTemplate/Attribute:wo_agent_id+' => '',
	'Class:WorkOrderTemplate/Attribute:wo_agent_name' => 'Agent',
	'Class:WorkOrderTemplate/Attribute:wo_agent_name+' => '',
	'Class:WorkOrderTemplate/Attribute:wo_duration' => 'Work duration',
	'Class:WorkOrderTemplate/Attribute:wo_duration+' => '',
	'Class:WorkOrderTemplate/Attribute:wo_team_id_friendlyname' => 'Team',
	'Class:WorkOrderTemplate/Attribute:wo_team_id_friendlyname+' => 'Team',
	'Class:WorkOrderTemplate/Attribute:wo_agent_id_friendlyname' => 'Agent',
	'Class:WorkOrderTemplate/Attribute:wo_agent_id_friendlyname+' => 'Agent',

	'UI:WorkOrderTemplate:HelpBlockLabel' => 'Help',
	'UI:WorkOrderTemplate:HelpBlockContent' => '<p>In the template data, you can use the placeholders <code>$context->att_code$</code> and <code>$template->att_code$</code> to substitute values from the context object and the template itself into the fields of the created work order.<p>
	<p>For example, if you specify the work order name in the template as <b>Work order for $context->ref$</b>, when creating a new work order using this template for a user request R-012345, it will be named <b>Work order for R-012345</b>. Substitution works similarly for other text fields of the template.</p>
	<p>You can also substitute values from objects associated with the context object. For example, <code>$context->caller_id->phone$</code> will be replaced with the phone number of the ticket caller.</p>',
));

//
// Class: WorkOrder
//

Dict::Add('EN US', 'English', 'English', array(
	'Class:WorkOrder/Attribute:template_key' => 'Template',
	'Class:WorkOrder/Attribute:template_key+' => 'Template used to create the work order',
));