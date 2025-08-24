<?php
/**
 * Localized data
 *
 * @copyright   Copyright (C) 2019-2022 Vladimir Kunin http://knowitop.ru
 */

Dict::Add('RU RU', 'Russian', 'Russian', array(
	'Class:Checklist' => 'Чек-лист',
	'Class:Checklist+' => 'Чек-лист',
	'Class:Checklist/Attribute:obj_key' => 'ID объекта',
	'Class:Checklist/Attribute:obj_key+' => 'ID объекта',
	'Class:Checklist/Attribute:obj_class' => 'Класс объекта',
	'Class:Checklist/Attribute:obj_class+' => 'Класс объекта',
	'Class:Checklist/Attribute:obj_org_id' => 'Организация',
	'Class:Checklist/Attribute:obj_org_id+' => 'Организация',
	'Class:Checklist/Attribute:title' => 'Название',
	'Class:Checklist/Attribute:title+' => 'Название',
	'Class:Checklist/Attribute:items_list' => 'Элементы',
	'Class:Checklist/Attribute:items_list+' => 'Элементы',
	'Class:Checklist/Attribute:template_key' => 'Шаблон',
	'Class:Checklist/Attribute:template_key+' => 'Номер шаблона, по которому создан объект',

	'Class:ChecklistItem' => 'Элемент чек-листа',
	'Class:ChecklistItem+' => 'Элемент чек-листа',
	'Class:ChecklistItem/Attribute:checklist_id' => 'Чек-лист',
	'Class:ChecklistItem/Attribute:checklist_id+' => 'Чек-лист',
	'Class:ChecklistItem/Attribute:checklist_title' => 'Чек-лист',
	'Class:ChecklistItem/Attribute:checklist_title+' => 'Чек-лист',
	'Class:ChecklistItem/Attribute:checklist_org_id' => 'Организация',
	'Class:ChecklistItem/Attribute:checklist_org_id+' => '',
	'Class:ChecklistItem/Attribute:text' => 'Текст',
	'Class:ChecklistItem/Attribute:text+' => '',
	'Class:ChecklistItem/Attribute:state' => 'Статус',
	'Class:ChecklistItem/Attribute:state+' => '',
	'Class:ChecklistItem/Attribute:created_at' => 'Создан в',
	'Class:ChecklistItem/Attribute:created_at+' => '',
	'Class:ChecklistItem/Attribute:checked_at' => 'Отмечен в',
	'Class:ChecklistItem/Attribute:checked_at+' => '',
	'Class:ChecklistItem/Attribute:checked_by' => 'Кем отмечен',
	'Class:ChecklistItem/Attribute:checked_by+' => '',
	'Class:ChecklistItem/Attribute:checklist_id_friendlyname' => 'Чек-лист',
	'Class:ChecklistItem/Attribute:checklist_id_friendlyname+' => '',

	'Class:ChecklistTemplate' => 'Шаблон чек-листа',
	'Class:ChecklistTemplate+' => 'Шаблон чек-листа',
	'Class:ChecklistTemplate/Name' => '%1$s',
	'Class:ChecklistTemplate/Attribute:checklist_title' => 'Название чек-листа',
	'Class:ChecklistTemplate/Attribute:checklist_title+' => 'Название чек-листа',
	'Class:ChecklistTemplate/Attribute:checklist_items' => 'Элементы чек-листа',
	'Class:ChecklistTemplate/Attribute:checklist_items+' => 'Элементы чек-листа построчно',

	'UI:ChecklistTemplate:HelpBlockLabel' => 'Помощь',
	'UI:ChecklistTemplate:HelpBlockContent' => '<p>В данных шаблона можно использовать заменители <code>$context->att_code$</code> и <code>$template->att_code$</code> для подстановки в поля создаваемого чек-лист значений из контекстного объекта и самого шаблона соответственно.<p>
	<p>Например, если в шаблоне указать название чек-листа <b>Чек-лист для $context->ref$</b>, при создании чек-листа по этому шаблону для запроса с номером R-012345, он будет назван <b>Чек-лист для R-012345</b>. Аналогично работает подстановка в элементах чек-листа.</p>
	<p>Также можно подставлять значения из связанных с контекстным объектов. Например, <code>$context->caller_id->phone$</code> будет заменён номером телефона инициатора тикета.</p>',

	'Checklist:Button:NewItem' => 'Добавить элемент...',
	'Checklist:NewItemPlaceholder' => 'Добавить элемент...',
	'Checklist:NewChecklistTitle' => 'Чек-лист',
	'Checklist:Button:Edit' => 'Изменить',
	'Checklist:Button:Delete' => 'Удалить...',
	'Checklist:Button:Save' => 'Сохранить',
	'Checklist:Button:Cancel' => 'Отменить',
	'Checklist:Button:NewList' => 'Создать чек-лист',
	'Checklist:Button:NewListFromTmpl' => 'Добавить из шаблона...',
	'Checklist:TabTitle_Count' => 'Чек-листы (%1$s)',
	'Checklist:EmptyTabTitle' => 'Чек-листы',
	'Checklist:TabTitle+' => 'Связанные чек-листы',

	'UI:Checklist:DlgPickATemplate' => 'Выберите шаблон',
	'UI:Checklist:DeleteDlg:Title' => 'Удалить чек-лист?',
	'UI:Checklist:DeleteDlg:Msg' => 'Чек-лист и все его элементы будут удалены. Это действие необратимо.',
	'UI:Checklist:DeleteDlg:Delete' => 'Удалить чек-лист',
	'UI:Checklist:DeleteDlg:Cancel' => 'Отменить',
	'UI:ChecklistItem:DeleteDlg:Title' => 'Удалить элемент?',
	'UI:ChecklistItem:DeleteDlg:Msg' => 'Элемент будет удалён из чек-листа без возможности восстановления.',
	'UI:ChecklistItem:DeleteDlg:Delete' => 'Удалить элемент',
	'UI:ChecklistItem:DeleteDlg:Cancel' => 'Отменить',

	'Class:WorkOrderTemplate/Attribute:checklist_templates_list' => 'Шаблоны чек-листов',
	'Class:WorkOrderTemplate/Attribute:checklist_templates_list+' => 'Связанные шаблоны чек-листов',

	'Class:lnkChecklistTemplateToWorkOrderTemplate' => 'Связь Шаблон чек-листа/Шаблон наряда',
	'Class:lnkChecklistTemplateToWorkOrderTemplate+' => '',
	'Class:lnkChecklistTemplateToWorkOrderTemplate/Attribute:checklist_template_id' => 'Шаблон чек-листа',
	'Class:lnkChecklistTemplateToWorkOrderTemplate/Attribute:checklist_template_id+' => '',
	'Class:lnkChecklistTemplateToWorkOrderTemplate/Attribute:workorder_template_id' => 'Шаблон наряда',
	'Class:lnkChecklistTemplateToWorkOrderTemplate/Attribute:workorder_template_id+' => '',
	'Class:lnkChecklistTemplateToWorkOrderTemplate/Attribute:checklist_template_id_friendlyname' => 'Шаблон чек-листа',
	'Class:lnkChecklistTemplateToWorkOrderTemplate/Attribute:checklist_template_id_friendlyname+' => '',
	'Class:lnkChecklistTemplateToWorkOrderTemplate/Attribute:workorder_template_id_friendlyname' => 'Шаблон наряда',
	'Class:lnkChecklistTemplateToWorkOrderTemplate/Attribute:workorder_template_id_friendlyname+' => '',
));