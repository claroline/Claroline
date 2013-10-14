(function () {
    'use strict';

    var displayedHomeTabId = $('#hometab-id-div').attr('hometab-id');
    var homeTabType = $('#hometab-type-div').attr('hometab-type-value');
    var currentElement;
    var currentHomeTabId;
    var currentHomeTabConfigId;
    var currentWidgetHomeTabConfigId;
    var currentWidgetInstanceId;

    function openFormModal(title, content)
    {
        $('#form-modal-title').html(title);
        $('#form-modal-body').html(content);
        $('#form-modal-box').modal('show');
    }

    function closeFormModal()
    {
        $('#form-modal-box').modal('hide');
        $('#form-modal-title').empty();
        $('#form-modal-body').empty();
    }

    // Click on + button to create a hometab
    $('#add-hometab-btn').click(function (e) {
        e.preventDefault();
        e.stopPropagation();

        $.ajax({
            url: Routing.generate('claro_admin_home_tab_create_form'),
            type: 'GET',
            success: function (datas) {
                openFormModal(
                    Translator.get('platform' + ':' + 'home_tab_creation'),
                    datas
                );
            }
        });
    });

    // Click on edit button of a hometab
    $('.hometab-rename-btn').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        currentElement = $(this).parents('.hometab-element');
        currentHomeTabId = currentElement.attr('hometab-id');

        $.ajax({
            url: Routing.generate(
                'claro_admin_home_tab_edit_form',
                {'homeTabId': currentHomeTabId}
            ),
            type: 'GET',
            success: function (datas) {
                openFormModal(
                    Translator.get('platform' + ':' + 'home_tab_edition'),
                    datas
                );
            }
        });
    });

    // Click on OK button of the Create HomeTab form modal
    $('body').on('click', '#form-create-hometab-ok-btn', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();

        var form = document.getElementById('create-hometab-form');
        var action = form.getAttribute('action');
        var formData = new FormData(form);

        var route = Routing.generate(
            action,
            {'homeTabType': homeTabType}
        );

        $.ajax({
            url: route,
            data: formData,
            type: 'POST',
            processData: false,
            contentType: false,
            complete: function(jqXHR) {
                switch (jqXHR.status) {
                    case 201:
                        closeFormModal();

                        var createRedirect = (homeTabType === 'desktop') ?
                            'claro_admin_desktop_home_tabs_configuration' :
                            'claro_admin_workspace_home_tabs_configuration';
                        window.location = Routing.generate(
                            createRedirect,
                            {'homeTabId': 0}
                        );
                        break;
                    case 204:
                        closeFormModal();

                        var renameRedirect = (homeTabType === 'desktop') ?
                            'claro_admin_desktop_home_tabs_configuration' :
                            'claro_admin_workspace_home_tabs_configuration';
                        window.location = Routing.generate(
                            renameRedirect,
                            {'homeTabId': currentHomeTabId}
                        );
                        break;
                    default:
                        $('#form-modal-body').html(jqXHR.responseText);
                }
            }
        });
    });

    // Click on OK button of the edit HomeTab form modal
    $('body').on('click', '#form-edit-hometab-ok-btn', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();

        var form = document.getElementById('edit-hometab-form');
        var action = form.getAttribute('action');
        var formData = new FormData(form);

        $.ajax({
            url: action,
            data: formData,
            type: 'POST',
            processData: false,
            contentType: false,
            complete: function(jqXHR) {
                switch (jqXHR.status) {
                    case 204:
                        closeFormModal();

                        var route = (homeTabType === 'desktop') ?
                            'claro_admin_desktop_home_tabs_configuration' :
                            'claro_admin_workspace_home_tabs_configuration';
                        window.location = Routing.generate(
                            route,
                            {'homeTabId': currentHomeTabId}
                        );
                        break;
                    default:
                        $('#form-modal-body').html(jqXHR.responseText);
                }
            }
        });
    });

    // Click on CANCEL button of the Create/Rename HomeTab form modal
    $('body').on('click', '#form-cancel-btn', function () {
        closeFormModal();
    });

    // Click on visibility button of a hometab
    $('.hometab-visibility-btn').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var visibilityBtn = $(this);
        currentElement = visibilityBtn.parents('.hometab-element');
        var homeTabConfigId = currentElement.attr('hometab-config-id');
        var visible = (visibilityBtn.attr('visiblility-value')).trim();
        var newVisible = (visible === 'visible') ? 'invisible' : 'visible';

        $.ajax({
            url: Routing.generate(
                'claro_admin_home_tab_update_visibility',
                {'homeTabConfigId': homeTabConfigId, 'visible': newVisible}
            ),
            type: 'POST',
            success: function () {
                if (newVisible === 'visible') {
                    visibilityBtn.attr('visiblility-value', 'visible')
                    visibilityBtn.removeClass('icon-eye-close');
                    visibilityBtn.addClass('icon-eye-open');
                } else {
                    visibilityBtn.attr('visiblility-value', 'invisible')
                    visibilityBtn.removeClass('icon-eye-open');
                    visibilityBtn.addClass('icon-eye-close');
                }
            }
        });
    });

    // Click on lock button of a hometab
    $('.hometab-lock-btn').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var lockBtn = $(this);
        currentElement = lockBtn.parents('.hometab-element');
        var homeTabConfigId = currentElement.attr('hometab-config-id');
        var locked = (lockBtn.attr('lock-value')).trim();
        var newLocked = (locked === 'locked') ? 'unlocked' : 'locked';

        $.ajax({
            url: Routing.generate(
                'claro_admin_home_tab_update_lock',
                {'homeTabConfigId': homeTabConfigId, 'locked': newLocked}
            ),
            type: 'POST',
            success: function () {
                if (newLocked === 'locked') {
                    lockBtn.attr('lock-value', 'locked')
                    lockBtn.removeClass('icon-unlock');
                    lockBtn.addClass('icon-lock');
                } else {
                    lockBtn.attr('lock-value', 'unlocked')
                    lockBtn.removeClass('icon-lock');
                    lockBtn.addClass('icon-unlock');
                }
            }
        });
    });

    // Click on left reorder button of a hometab
    $('.hometab-reorder-left-btn').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        currentElement = $(this).parents('.hometab-element');
        var homeTabConfigId = currentElement.attr('hometab-config-id');

        $.ajax({
            url: Routing.generate(
                'claro_admin_home_tab_config_change_order',
                {'homeTabConfigId': homeTabConfigId, 'direction': -1}
            ),
            type: 'POST',
            success: function (data) {
                if (data === '-1') {
                    var previousSibling = currentElement.prev();
                    previousSibling.before(currentElement);
                }
            }
        });
    });

    // Click on right reorder button of a hometab
    $('.hometab-reorder-right-btn').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        currentElement = $(this).parents('.hometab-element');
        var homeTabConfigId = currentElement.attr('hometab-config-id');

        $.ajax({
            url: Routing.generate(
                'claro_admin_home_tab_config_change_order',
                {'homeTabConfigId': homeTabConfigId, 'direction': 1}
            ),
            type: 'POST',
            success: function (data) {
                if (data === '1') {
                    var nextSibling = currentElement.next();
                    nextSibling.after(currentElement);
                }
            }
        });
    });

    // Click on delete button of a hometab
    $('.hometab-delete-btn').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        currentElement = $(this).parents('.hometab-element');
        currentHomeTabId = currentElement.attr('hometab-id');
        currentHomeTabConfigId = currentElement.attr('hometab-config-id');
        $('#delete-hometab-validation-box').modal('show');
    });

    // Click on OK button of hometab delete confirmation modal
    $('#delete-hometab-confirm-ok').click(function () {
        $.ajax({
            url: Routing.generate(
                'claro_admin_home_tab_config_delete',
                {'homeTabConfigId': currentHomeTabConfigId}
            ),
            type: 'DELETE',
            success: function () {
                $('#delete-hometab-validation-box').modal('hide');

                if (displayedHomeTabId === currentHomeTabId) {
                    var route = (homeTabType === 'desktop') ?
                        'claro_admin_desktop_home_tabs_configuration' :
                        'claro_admin_workspace_home_tabs_configuration';
                    window.location = Routing.generate(
                        route,
                        {'homeTabId': -1}
                    );
                } else {
                    currentElement.remove();
                }
            }
        });
    });

    // Click on widget create button
    $('.add-widget-instance').on('click', function () {
        $.ajax({
            url: Routing.generate(
                'claro_admin_widget_instance_create_form',
                {'homeTabType': homeTabType}
            ),
            type: 'GET',
            success: function (datas) {
                openFormModal(
                    Translator.get('platform' + ':' + 'create_widget_instance'),
                    datas
                );
            }
        });
    });

    // Click on OK button of the Create Widget instance form modal
    $('body').on('click', '#form-create-widget-instance-ok-btn', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();

        var form = document.getElementById('create-widget-instance-form');
        var action = form.getAttribute('action');
        var formData = new FormData(form);

        $.ajax({
            url: action,
            data: formData,
            type: 'POST',
            processData: false,
            contentType: false,
            success: function(datas, textStatus, jqXHR) {
                switch (jqXHR.status) {
                    case 201:
                        var widgetInstanceId = parseInt(datas);

                        $.ajax({
                            url: Routing.generate(
                                'claro_admin_associate_widget_to_home_tab',
                                {
                                    'homeTabId': displayedHomeTabId,
                                    'widgetInstanceId': widgetInstanceId
                                }
                            ),
                            type: 'POST',
                            success: function () {
                                closeFormModal();
                                window.location.reload();
                            }
                        });
                        break;
                    default:
                        $('#form-modal-body').html(jqXHR.responseText);
                }
            }
        });
    });

    // Click on delete button of a widget
    $('.widget-delete-btn').click(function () {
        currentElement = $(this).parents('.widget-instance-panel');
        currentWidgetHomeTabConfigId = currentElement.attr('widget-hometab-config-id');
        $('#delete-widget-hometab-validation-box').modal('show');
    });

    // Click on OK button of widget delete confirmation modal
    $('#delete-widget-hometab-confirm-ok').click(function () {
        $.ajax({
            url: Routing.generate(
                'claro_admin_widget_home_tab_config_delete',
                {'widgetHomeTabConfigId': currentWidgetHomeTabConfigId}
            ),
            type: 'DELETE',
            success: function () {
                window.location.reload();
            }
        });
    });

    // Click on the widget rename button
    $('.widget-instance-rename').on('click', function () {
        currentElement = $(this).parents('.widget-instance-panel');
        currentWidgetInstanceId = currentElement.attr('widget-instance-id');

        $.ajax({
            url: Routing.generate(
                'claro_admin_widget_instance_name_edit_form',
                {'widgetInstanceId': currentWidgetInstanceId}
            ),
            type: 'GET',
            success: function (datas) {
                openFormModal(
                    Translator.get('platform' + ':' + 'rename_widget_instance'),
                    datas
                );
            }
        });
    });

    // Click on OK button of the Rename Widget form modal
    $('body').on('click', '#form-edit-widget-instance-ok-btn', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();

        var form = document.getElementById('edit-widget-instance-form');
        var action = form.getAttribute('action');
        var formData = new FormData(form);

        $.ajax({
            url: action,
            data: formData,
            type: 'POST',
            processData: false,
            contentType: false,
            complete: function(jqXHR) {
                switch (jqXHR.status) {
                    case 204:
                        var value = $('#widget_display_form_name').val();
                        currentElement.find('.widget-instance-name').html(value);
                        closeFormModal();
                        break;
                    default:
                        $('#form-modal-body').html(jqXHR.responseText);
                }
            }
        });
    });

    // Click on widget order-up button
    $('#widgets-list-panel').on('click', '.widget-order-up', function () {
        currentElement = $(this).parents('.widget-instance-panel');
        currentWidgetHomeTabConfigId = currentElement.attr('widget-hometab-config-id');

        $.ajax({
            url: Routing.generate(
                'claro_admin_widget_home_tab_config_change_order',
                {
                    'widgetHomeTabConfigId': currentWidgetHomeTabConfigId,
                    'direction': -1
                }
            ),
            type: 'POST',
            success: function (data) {
                if (data === '-1') {
                    var previousSibling = currentElement.prev();
                    previousSibling.before(currentElement);
                    var currentOrderBtns = currentElement.find('.widget-order-btn-group');
                    var previousOrderBtns = previousSibling.find('.widget-order-btn-group');
                    var currentHtml = currentOrderBtns.html();
                    var previousHtml = previousOrderBtns.html();
                    currentOrderBtns.html(previousHtml);
                    previousOrderBtns.html(currentHtml);
                }
            }
        });
    });

    // Click on widget order-down button
    $('#widgets-list-panel').on('click', '.widget-order-down', function () {
        currentElement = $(this).parents('.widget-instance-panel');
        currentWidgetHomeTabConfigId = currentElement.attr('widget-hometab-config-id');

        $.ajax({
            url: Routing.generate(
                'claro_admin_widget_home_tab_config_change_order',
                {
                    'widgetHomeTabConfigId': currentWidgetHomeTabConfigId,
                    'direction': 1
                }
            ),
            type: 'POST',
            success: function (data) {
                if (data === '1') {
                    var nextSibling = currentElement.next();
                    nextSibling.after(currentElement);
                    var currentOrderBtns = currentElement.find('.widget-order-btn-group');
                    var nextOrderBtns = nextSibling.find('.widget-order-btn-group');
                    var currentHtml = currentOrderBtns.html();
                    var nextHtml = nextOrderBtns.html();
                    currentOrderBtns.html(nextHtml);
                    nextOrderBtns.html(currentHtml);
                }
            }
        });
    });

    // Click on widget visibility button
    $('.widget-visibility-btn').on('click', function () {
        var visibilityBtn = $(this);
        currentElement = visibilityBtn.parents('.widget-instance-panel');
        currentWidgetHomeTabConfigId = currentElement.attr('widget-hometab-config-id');
        var visible = (visibilityBtn.attr('visiblility-value')).trim();
        var newVisible = (visible === 'visible') ? 'invisible' : 'visible';

        $.ajax({
            url: Routing.generate(
                'claro_admin_widget_home_tab_config_change_visibility',
                {'widgetHomeTabConfigId': currentWidgetHomeTabConfigId}
            ),
            type: 'POST',
            success: function () {
                if (newVisible === 'visible') {
                    visibilityBtn.attr('visiblility-value', 'visible')
                    visibilityBtn.removeClass('icon-eye-close');
                    visibilityBtn.addClass('icon-eye-open');
                } else {
                    visibilityBtn.attr('visiblility-value', 'invisible')
                    visibilityBtn.removeClass('icon-eye-open');
                    visibilityBtn.addClass('icon-eye-close');
                }
            }
        });
    });

    // Click on widget lock button
    $('.widget-lock-btn').on('click', function () {
        var lockBtn = $(this);
        currentElement = lockBtn.parents('.widget-instance-panel');
        currentWidgetHomeTabConfigId = currentElement.attr('widget-hometab-config-id');
        var locked = (lockBtn.attr('lock-value')).trim();
        var newLocked = (locked === 'locked') ? 'unlocked' : 'locked';

        $.ajax({
            url: Routing.generate(
                'claro_admin_widget_home_tab_config_change_lock',
                {'widgetHomeTabConfigId': currentWidgetHomeTabConfigId}
            ),
            type: 'POST',
            success: function () {
                if (newLocked === 'locked') {
                    lockBtn.attr('lock-value', 'locked')
                    lockBtn.removeClass('icon-unlock');
                    lockBtn.addClass('icon-lock');
                } else {
                    lockBtn.attr('lock-value', 'unlocked')
                    lockBtn.removeClass('icon-lock');
                    lockBtn.addClass('icon-unlock');
                }
            }
        });
    });

    // Click on widget configuration button
    $('.widget-instance-config').on('click', function () {
        var configButton = $(this);
        currentElement = configButton.parents('.widget-instance-panel');
        currentWidgetInstanceId = currentElement.attr('widget-instance-id');

        $.ajax({
            url: Routing.generate(
                'claro_admin_widget_configuration',
                {'widgetInstance': currentWidgetInstanceId}
            ),
            type: 'GET',
            success: function (datas) {
                configButton.addClass('hide');
                var widgetEditionElement = currentElement.find('.widget-instance-edition-element');
                widgetEditionElement.html(datas);
                widgetEditionElement.removeClass('hide');
                var textArea = $('textarea', datas)[0];

                if (textArea) {
                    initTinyMCE(stfalcon_tinymce_config);
                }
            }
        });
    });

    // Click on CANCEL button of the configuration Widget form
    $('.widget-instance-edition-element').on(
        'click',
        '.claro-widget-form-cancel',
        function (e) {
            e.stopImmediatePropagation();
            e.preventDefault();
            var widgetElement = $(this).parents('.widget-instance-panel');
            var widgetEditionElement = widgetElement.find('.widget-instance-edition-element');
            widgetEditionElement.addClass('hide');
            widgetEditionElement.empty();
            var configButton = widgetElement.find('.widget-instance-config');
            configButton.removeClass('hide');
        }
    );

    // Click on OK button of the configuration Widget form
    $('#widgets-list-panel').on(
        'submit',
        '.widget-instance-edition-element > form',
        function(e) {
            e.stopImmediatePropagation();
            e.preventDefault();

            var form = e.currentTarget;
            var action = $(e.currentTarget).attr('action');
            var formData = new FormData(form);

            var widgetElement = $(this).parents('.widget-instance-panel');
            var configButton = widgetElement.find('.widget-instance-config');
            var widgetEditionElement = widgetElement.find('.widget-instance-edition-element');

            $.ajax({
                url: action,
                data: formData,
                type: 'POST',
                processData: false,
                contentType: false,
                complete: function(jqXHR) {
                    switch (jqXHR.status) {
                        case 204:
                            widgetEditionElement.addClass('hide');
                            widgetEditionElement.empty();
                            configButton.removeClass('hide');
                            break;
                        default:
                            widgetEditionElement.html(jqXHR.responseText);
                    }
                }
            });
        }
    );
})();