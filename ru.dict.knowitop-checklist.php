<?php
/**
 * Localized data
 *
 * @copyright   Copyright (C) 2017 Vladimir Kunin <v.b.kunin@gmail.com>
 *
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
        'Class:ChecklistItem/Attribute:state' => 'Статсу',
        'Class:ChecklistItem/Attribute:state+' => '',
        'Class:ChecklistItem/Attribute:created_at' => 'Создан в',
        'Class:ChecklistItem/Attribute:created_at+' => '',
        'Class:ChecklistItem/Attribute:checked_at' => 'Отмечен в',
        'Class:ChecklistItem/Attribute:checked_at+' => '',
        'Class:ChecklistItem/Attribute:checklist_id_friendlyname' => 'Чек-лист',
        'Class:ChecklistItem/Attribute:checklist_id_friendlyname+' => '',

        'Class:ChecklistTemplate' => 'Шаблон чек-листа',
        'Class:ChecklistTemplate+' => 'Шаблон чек-листа',
        'Class:ChecklistTemplate/Name' => '%1$s',
        'Class:ChecklistTemplate/Attribute:title' => 'Название чек-листа',
        'Class:ChecklistTemplate/Attribute:title+' => 'Название чек-листа',
        'Class:ChecklistTemplate/Attribute:items_text' => 'Элементы чек-листа',
        'Class:ChecklistTemplate/Attribute:items_text+' => 'Элементы чек-листа построчно',

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
    )
);