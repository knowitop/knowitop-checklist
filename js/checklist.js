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
    this.titleContainer = this.container.find('.checklist-title');
    this.titleInput = this.container.find("input[name='listTitle']");

    this.newItemContainer = this.container.find('.checklist-new-item');
    this.newItemInput = this.container.find("input[name='newItemText']");

    this.id = param.id || this.container.data('checklist-id');
    // this.name = param.name || this.container.data('checklist-name');
    this.title = param.title || this.titleInput.val();
    this.items = [];

    this.initItems();
    this.setListeners();
    // console.log(this);
}

Checklist.prototype.remove = function () {
    var data = {
        operation: 'remove_list',
        checklist_id: this.id
    };
    checklistApi.post(data, function (err, data) {
        if (err) {
            alert(err.message);
            console.error(err.message);
            // TODO: show error to user
        } else {
            this.container.remove();
            delete this;
        }
    }.bind(this));
};

Checklist.prototype.saveTitle = function () {
    var data = {
        operation: 'edit_list',
        checklist_id: this.id,
        title: this.titleInput.val()
    };
    if (data.title.trim() === '') return this.titleInput.focus();
    checklistApi.post(data, function (err, data) {
        // console.log(data);
        if (err) {
            alert(err.message);
            console.error(err.message);
            // TODO: show error message to user
        } else {
            this.titleInput.width(160);
            this.title = this.titleInput.val();
            this.titleContainer.children('.checklist-title-text').text(this.title);
            this.titleContainer.removeClass('checklist-title-edit-mode');
        }
    }.bind(this));
};

Checklist.prototype.editTitle = function () {
    this.titleInput.width(Math.max(this.titleInput.width(), this.titleContainer.children('.checklist-title-text').width() + 5));
    this.titleContainer.addClass('checklist-title-edit-mode');
    this.titleInput.focus();
};

Checklist.prototype.cancelTitle = function () {
    this.titleInput.val(this.title);
    this.titleContainer.removeClass('checklist-title-edit-mode');
};

Checklist.prototype.saveItem = function () {
    var data = {
        operation: 'add_item',
        checklist_id: this.id,
        text: this.newItemInput.val()
    };
    if (data.text.trim() === '') return this.newItemInput.focus();
    checklistApi.post(data, function (err, data) {
        if (err) {
            alert(err.message);
            console.error(err.message);
            // TODO: show pretty error to user
        } else {
            this.container.find('.checklist-items').append('<li>' + data.html + '</li>');
            this.items.push(new ChecklistItem({ id: data.id, listId: this.id }));
            this.newItemInput.val('');
        }
    }.bind(this));
};

Checklist.prototype.newItem = function () {
    this.newItemContainer.addClass('checklist-item-edit-mode');
    this.newItemInput.focus();
};

Checklist.prototype.cancelItem = function () {
    this.newItemInput.val('');
    this.newItemContainer.removeClass('checklist-item-edit-mode');
};

Checklist.prototype.setListeners = function () {
    this.newItemContainer.find('a.checklist-btn').click(function (event) {
        var action = $(event.target).data('checklist-action');
        switch (action) {
            case 'new_item':
                this.newItem();
                break;
            case 'save_item':
                this.saveItem();
                break;
            case 'cancel_item':
                this.cancelItem();
                break;
        }
    }.bind(this));

    this.newItemInput.keydown(function (event) {
        if (event.keyCode === 13) this.saveItem();
        else if (event.keyCode === 27) this.cancelItem();
    }.bind(this));

    this.titleContainer.find('a.checklist-btn').click(function (event) {
        var action = $(event.target).data('checklist-action');
        switch (action) {
            case 'edit':
                this.editTitle();
                break;
            case 'save':
                this.saveTitle();
                break;
            case 'cancel':
                this.cancelTitle();
                break;
            case 'delete':
                var deleteMessage = Dict.S('UI:Checklist:DeleteDlg:Msg');
                var checklist = this;
                $('<div>' + deleteMessage + '</div>').dialog({
                    modal: true,
                    title: Dict.S('UI:Checklist:DeleteDlg:Title'),
                    close: function() { $(this).remove(); },
                    minWidth: 400,
                    buttons: [
                        { text: Dict.S('UI:Checklist:DeleteDlg:Cancel'), click: function() { $(this).dialog('close'); } },
                        { text: Dict.S('UI:Checklist:DeleteDlg:Delete'), click: function() { checklist.remove(); $(this).dialog('close'); } }
                    ]
                });
                break;
        }
    }.bind(this));

    this.titleInput.keydown(function (event) {
        if (event.keyCode === 13) this.saveTitle();
        else if (event.keyCode === 27) this.cancelTitle();
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
    this.textInput = this.container.find("input[name='itemText']");

    this.listId = param.listId;
    this.id = param.id || this.container.data('item-id');
    this.text = param.text || this.textInput.val();
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
                alert(err.message);
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
                this.textInput.width(Math.max(this.textInput.width(), this.container.children('.checklist-item-text').width() + 5));
                this.container.addClass('checklist-item-edit-mode');
                this.textInput.focus(); // .val(this.textInput.val()); // put cursor at the end
                break;

            case 'cancel':
                this.cancel();
                break;

            case 'save':
                this.saveText();
                break;

            case 'delete':
                var deleteMessage = Dict.S('UI:ChecklistItem:DeleteDlg:Msg');
                var item = this;
                $('<div>' + deleteMessage + '</div>').dialog({
                    modal: true,
                    title: Dict.S('UI:ChecklistItem:DeleteDlg:Title'),
                    close: function() { $(this).remove(); },
                    minWidth: 400,
                    buttons: [
                        { text: Dict.S('UI:ChecklistItem:DeleteDlg:Cancel'), click: function() { $(this).dialog('close'); } },
                        { text: Dict.S('UI:ChecklistItem:DeleteDlg:Delete'), click: function() { item.remove(); $(this).dialog('close'); } }
                    ]
                });
                break;
        }
    }.bind(this));

    this.textInput.keydown(function (event) {
        if (event.keyCode === 13) this.saveText();
        else if (event.keyCode === 27) this.cancel();
    }.bind(this));
};

ChecklistItem.prototype.saveText = function () {
    var data = {
        operation: 'edit_item',
        id: this.id,
        text: this.textInput.val()
    };
    if (data.text.trim() === '') return this.textInput.focus();
    checklistApi.post(data, function (err, data) {
        // console.log(data);
        if (err) {
            alert(err.message);
            console.error(err.message);
            // TODO: show error message to user
        } else {
            this.textInput.width(160);
            this.text = this.textInput.val();
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
            alert(err.message);
            console.error(err);
        } else {
            console.log(data);
            this.container.parent('li').remove();
        }
    }.bind(this));
};

ChecklistItem.prototype.cancel = function () {
    this.textInput.val(this.text).width(160);
    this.container.removeClass('checklist-item-edit-mode');
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
            if (err) {
                alert(err.message);
                return console.error(err.message);
            }
            console.log(data);
            $('.checklist-new-list').before(data.html);
            (new Checklist({ id: data.id })).newItem();
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
                title: Dict.S('UI:Checklist:DlgPickATemplate'),
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
            if (err) {
                alert(err.message);
                return console.error(err.message);
            }
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
