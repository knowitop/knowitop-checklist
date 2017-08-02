function ChecklistAPI(param) {
    this.url = param.url;
    this.hostId = param.hostId;
    this.hostClass =  param.hostClass;
    this.post = function checklistPostData (data, cb) {
        data.host_id = this.hostId;
        data.host_class = this.hostClass;
        $.post(this.url, data, function (result) {
            try {
                var data = JSON.parse(result);
            } catch (e) {
                return cb(new Error(result));
            }
            if (data.error !== false) return cb(new Error(data.message));
            return cb(null, data);
        });
    };
}

function Checklist(param) {
    this.container = param.container ? $(param.container) : $("[data-checklist-id='" + param.id + "']");
    this.newItemContainer = this.container.find('.checklist-new-item');
    this.newItemInput = this.container.find("input[name='newItemText']");

    this.id = param.id || this.container.data('checklist-id');
    this.name = param.name || this.container.data('checklist-name');
    this.items = [];

    this.initItems();
    this.setListeners();

    console.log(this);
}

Checklist.prototype.remove = function () {
    var data = {
        operation: 'remove_list',
        id: this.id
    };
    checklistApi.post(data, function (err, data) {
        if (err) {
            console.error(err.message);
            // TODO: show error to user
        } else {
            this.container.remove();
            delete this;
        }
    }.bind(this));
};

Checklist.prototype.setListeners = function () {
    this.newItemContainer.find('a.checklist-btn').click(function (event) {
        var action = $(event.target).data('checklist-action');
        switch (action) {
            case 'new_item':
                this.newItemContainer.addClass('checklist-item-edit-mode');
                this.newItemInput.focus();
                break;
            case 'save_item':
                var data = {
                    operation: 'add_item',
                    checklist_id: this.id,
                    text: this.newItemInput.val()
                };
                checklistApi.post(data, function (err, data) {
                    if (err) {
                        alert(err.message);
                        console.error(err.message);
                        // TODO: show error to user
                    } else {
                        this.container.find('.checklist-items').append('<li>' + data.html + '</li>');
                        this.items.push(new ChecklistItem({ id: data.id, listId: this.id }));
                        this.newItemInput.val('');
                    }
                }.bind(this));
                break;
            case 'cancel_item':
                this.newItemInput.val('');
                this.newItemContainer.removeClass('checklist-item-edit-mode');
                break;
            case 'remove_list':
                // todo: translate
                var deleteMessage = 'Чек-лист и все его элементы будут удалены. Это действие необратимо.';
                var checklist = this;
                $('<div>' + deleteMessage + '</div>').dialog({
                    modal: true,
                    title: 'Удаление чек-листа',
                    close: function() { $(this).remove(); },
                    minWidth: 400,
                    buttons: [
                        { text: 'Отменить', click: function() { $(this).dialog('close'); } },
                        { text: 'Удалить чек-лист', click: function() { checklist.remove(); $(this).dialog('close'); } }
                    ]
                });
                break;
        }
    }.bind(this));
};

Checklist.prototype.initItems = function () {
    this.container.find(".checklist-item").each(function (index, itemContainer) {
        this.items.push(new ChecklistItem({ container: itemContainer, listId: this.id }));
    }.bind(this));
};

function ChecklistItem(param) {
    this.container = param.container ? $(param.container) : $("[data-item-id='" + param.id + "']");
    this.checkbox = this.container.find("input[name='itemState']");

    this.listId = param.listId;
    this.id = param.id || this.container.data('item-id');
    this.text = param.text || this.container.find("input[name='itemText']").val();
    this.state = param.state || this.checkbox.prop('checked');

    this.setListeners();
    // console.log(this);
}

ChecklistItem.prototype.setListeners = function () {

    this.checkbox.change(function () {
        this.state = !this.state; // update state
        var data = {
            operation: 'check_item',
            id: this.id,
            state: this.state ? 'complete' : 'incomplete' // send new state
        };
        checklistApi.post(data, function(err, data) {
            console.log(data);
            if (err) {
                this.state = !this.state; // rollback state when err
                this.checkbox.prop('checked', this.state);
                console.error(err.message);
                alert(err.message);
                // TODO: show error message to user
            } else {
                this.container.toggleClass('checklist-item-state-complete');
                this.container.find('.checklist-item-checked-at').text(data.checked_at)
            }
        }.bind(this));
    }.bind(this));

    this.container.find('a.checklist-btn').click(function (event) {
        var action = $(event.target).data('item-action');
        switch (action) {
            case 'edit':
                var input = this.container.children('.checklist-item-text-input');
                input.width(Math.max(input.width(), this.container.children('.checklist-item-text').width() + 5));
                this.container.addClass('checklist-item-edit-mode');
                input.focus().val(input.val()); // put cursor at the end
                break;

            case 'cancel':
                this.container.children('.checklist-item-text-input').val(this.text).width(160);
                this.container.removeClass('checklist-item-edit-mode');
                break;

            case 'apply':
                this.saveText();
                break;

            case 'remove':
                // todo: translate
                // var deleteMessage = 'Чек-лист и все его элементы будут удалены. Это действие необратимо.';
                var deleteMessage = 'Элемент будет удалён из чек-листа без возможности восстановления.';
                var item = this;
                $('<div>' + deleteMessage + '</div>').dialog({
                    modal: true,
                    title: 'Удаление элемена',
                    close: function() { $(this).remove(); },
                    minWidth: 400,
                    buttons: [
                        { text: 'Отменить', click: function() { $(this).dialog('close'); } },
                        { text: 'Удалить элемент', click: function() { item.remove(); $(this).dialog('close'); } }
                    ]
                });
                break;
        }
    }.bind(this));
};

ChecklistItem.prototype.saveText = function () {
    this.text = this.container.children('.checklist-item-text-input').val();
    var data = {
        operation: 'edit_item',
        id: this.id,
        text: this.text
    };
    checklistApi.post(data, function (err, data) {
        console.log(data);
        if (err) {
            console.error(err.message);
            // TODO: show error message to user
        } else {
            this.container.children('.checklist-item-text-input').width(160);
            this.container.children('.checklist-item-text').text(this.text);
            this.container.removeClass('checklist-item-edit-mode');
        }
    }.bind(this));
};

ChecklistItem.prototype.remove = function () {
    var data = {
        operation: 'remove_item',
        id: this.id
    };
    checklistApi.post(data, function (err, data) {
        if (err) {
            console.error(err);
        } else {
            console.log(data);
            this.container.parent('li').remove();
        }
    }.bind(this));
};

$(function () {
    // $('.checklist-items').sortable().disableSelection();

    $('.checklist').each(function () {
        new Checklist({ container: this });
    });

    $("[data-checklist-action='create_list']").click(function () {
        var postData = {
            operation: 'create_list',
            edit_mode: window.location.search.substr(1).split('&')[0].split('=')[1] === 'modify' ? 1 : 0
        };
        checklistApi.post(postData, function(err, data) {
            if (err) return console.error(err.message);
            console.log(data);
            $('.checklist-new-list').before(data.html);
            new Checklist({ id: data.id });
        });
    });

    $("[data-checklist-action='create_list_from_template']").click(function () {
        SelectTemplate();
    });

    // $('#new_list_from_template').click(function () {
    //     SelectTemplate();
    // });

    // $('#new_list').click(function () {
    //     SelectTemplate();
    // });
});


function SelectTemplate()
{
    if ($('#template_button').attr('disabled')) return; // Disabled, do nothing
    if ($('#template_dlg').length === 0)
    {
        $('body').append('<div id="template_dlg"></div>');
    }
    $('#template_button').attr('disabled', 'disabled');
    $('#v_template').html('<img src="../images/indicator.gif" />');

    // oWizardHelper.UpdateWizard();
    var postData = {
        // json: oWizardHelper.ToJSON(),
        operation: 'select_template'
    };

    // Run the query and get the result back directly in HTML
    $.post(checklistApi.url, postData,
        function(data)
        {
            var dlg = $('#template_dlg');
            dlg.html(data);
            dlg.dialog({
                width: 'auto',
                height: 'auto',
                autoOpen: false,
                modal: true,
                title: Dict.S('UI:Dlg-PickATemplate'),
                resizeStop: function(event, ui) { TemplateUpdateSizes(); },
                close: function() { OnCloseTemplate(); }
            });
            var data_area = $('#dr_template_select');
            data_area.css('max-height', (0.5*$(document).height())+'px'); // Stay within the document's boundaries
            data_area.css('overflow', 'auto'); // Stay within the document's boundaries
            dlg.dialog('open');
            TemplateDoSearch();
            $('#template_select').resize(function() { TemplateUpdateSizes(); });
        },
        'html'
    );
}

function TemplateDoSearch()
{
    var theMap = {};

    // Gather the parameters from the search form
    $('#fs_template_select :input').each( function() {
        if (this.name !== '')
        {
            var val = $(this).val(); // supports multiselect as well
            if (val !== null)
            {
                theMap[this.name] = val;
            }
        }
    });
    theMap['operation'] = 'search_template';
    // theMap['log_attcode'] = sLogAttCode;

    // Run the query and get the result back directly in HTML
    $.post(checklistApi.url, theMap,
        function(data)
        {
            var res = $('#dr_template_select');
            res.html(data);
            TemplateUpdateSizes();
        },
        'html'
    );
    return false; // Stay on page
}

function TemplateUpdateSizes()
{
    var dlg = $('#template_dlg');
    // Adjust the dialog's size to fit into the screen
    dlg.dialog('option', 'position', 'center');

    var searchForm = $('#template_select');
    var results = $('#fr_template_select');

    var padding_right = 0;
    if (dlg.css('padding-right'))
    {
        padding_right = parseInt(dlg.css('padding-right').replace('px', ''));
    }
    var padding_left = 0;
    if (dlg.css('padding-left'))
    {
        padding_left = parseInt(dlg.css('padding-left').replace('px', ''));
    }
    var padding_top = 0;
    if (dlg.css('padding-top'))
    {
        padding_top = parseInt(dlg.css('padding-top').replace('px', ''));
    }
    var padding_bottom = 0;
    if (dlg.css('padding-bottom'))
    {
        padding_bottom = parseInt(dlg.css('padding-bottom').replace('px', ''));
    }
    width = dlg.innerWidth() - padding_right - padding_left - 22; // 5 (margin-left) + 5 (padding-left) + 5 (padding-right) + 5 (margin-right) + 2 for rounding !
    height = dlg.innerHeight() - padding_top - padding_bottom -22;
    height = Math.max(height, 350); // Ensure there is enough space for at least one line...
    wizard = dlg.find('.wizContainer:first');
    wizard.width(Math.max(wizard.width(), width+22));
    wizard.height(height);
    form_height = searchForm.outerHeight();
    results.height(height - form_height - 40); // Leave some space for the buttons
}

function OnCloseTemplate()
{
    $('#template_button').removeAttr('disabled');
    $('#v_template').html('');
}

function TemplateDoSelect()
{
    // TODO: множетсвенный выбор шаблонов
    var selected = $('input.selectListtemplate_select_results:checked');
    if (selected.length > 0)
    {
        var aSelected = new Array();
        var index = 0;
        selected.each( function () { aSelected[index++] = this.value; });

        // oWizardHelper.UpdateWizard();
        var postData = {
            // 'json': oWizardHelper.ToJSON(),
            operation: 'create_list_from_template',
            selected: aSelected,
            edit_mode: window.location.search.substr(1).split('&')[0].split('=')[1] === 'modify' ? 1 : 0
        };

        checklistApi.post(postData, function(err, data) {
            if (err) return console.error(err.message);
            console.log(data);
            $('.checklist-new-list').before(data.html);
            new Checklist({ id: data.id });
        });
        // Run the query and get the result back directly in HTML
        // $.post(checklistApi.url, postData,
        //     function(data)
        //     {
        //         console.log(data);
        //         $('.checklist-new-list').before(data.html);
        //         new Checklist({ id: data.id });
        //     },
        //     'json'
        // );


    }
    var dlg = $('#template_dlg');
    dlg.dialog('close');
    dlg.html('');
}
