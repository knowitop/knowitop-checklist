<?php
/**
 * Localized data
 *
 * @copyright   Copyright (C) 2019-2022 Vladimir Kunin https://knowitop.ru
 */

//
// Class: WorkOrderTemplate
//

Dict::Add('RU RU', 'Russian', 'Русский', array(
	'Class:WorkOrderTemplate' => 'Шаблон наряда на работу',
	'Class:WorkOrderTemplate+' => '',
	'Class:WorkOrderTemplate/Attribute:wo_name' => 'Название наряда',
	'Class:WorkOrderTemplate/Attribute:wo_name+' => '',
	'Class:WorkOrderTemplate/Attribute:wo_description' => 'Описание наряда',
	'Class:WorkOrderTemplate/Attribute:wo_description+' => '',
	'Class:WorkOrderTemplate/Attribute:wo_team_id' => 'Команда',
	'Class:WorkOrderTemplate/Attribute:wo_team_id+' => '',
	'Class:WorkOrderTemplate/Attribute:wo_team_name' => 'Команда',
	'Class:WorkOrderTemplate/Attribute:wo_team_name+' => '',
	'Class:WorkOrderTemplate/Attribute:wo_agent_id' => 'Агент',
	'Class:WorkOrderTemplate/Attribute:wo_agent_id+' => '',
	'Class:WorkOrderTemplate/Attribute:wo_agent_name' => 'Агент',
	'Class:WorkOrderTemplate/Attribute:wo_agent_name+' => '',
	'Class:WorkOrderTemplate/Attribute:wo_duration' => 'Продолжительность работ',
	'Class:WorkOrderTemplate/Attribute:wo_duration+' => '',
	'Class:WorkOrderTemplate/Attribute:wo_team_id_friendlyname' => 'Команда',
	'Class:WorkOrderTemplate/Attribute:wo_team_id_friendlyname+' => 'Команда',
	'Class:WorkOrderTemplate/Attribute:wo_agent_id_friendlyname' => 'Агент',
	'Class:WorkOrderTemplate/Attribute:wo_agent_id_friendlyname+' => 'Агент',

	'UI:WorkOrderTemplate:HelpBlockLabel' => 'Помощь',
	'UI:WorkOrderTemplate:HelpBlockContent' => '<p>В данных шаблона можно использовать заменители <code>$context->att_code$</code> и <code>$template->att_code$</code> для подстановки в поля создаваемого наряда значений из контекстного объекта и самого шаблона соответственно.<p>
	<p>Например, если в шаблоне указать название наряда <b>Наряд для $context->ref$</b>, при создании наряда по этому шаблону для запроса с номером R-012345, он будет назван <b>Наряд для R-012345</b>. Аналогично работает подстановка в других текстовых полях.</p>
	<p>Также можно подставлять значения из связанных с контекстным объектов. Например, <code>$context->caller_id->phone$</code> будет заменён номером телефона инициатора тикета.</p>',
));

//
// Class: WorkOrder
//

Dict::Add('RU RU', 'Russian', 'Русский', array(
	'Class:WorkOrder/Attribute:template_key' => 'Шаблон',
	'Class:WorkOrder/Attribute:template_key+' => 'Номер шаблона, по которому создан объект',
));