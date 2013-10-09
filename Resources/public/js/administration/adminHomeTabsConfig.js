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

        currentHomeTabId = $(this).parent().attr('hometab-id');

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
        var homeTabConfigId = visibilityBtn.parent().attr('hometab-config-id');
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
        var homeTabConfigId = lockBtn.parent().attr('hometab-config-id');
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

    // Click on delete button of a hometab
    $('.hometab-delete-btn').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        currentElement = $(this).parent().parent().parent();
        currentHomeTabId = $(this).parent().attr('hometab-id');
        currentHomeTabConfigId = $(this).parent().attr('hometab-config-id');
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
        currentWidgetHomeTabConfigId = $(this).parent().parent().parent().attr('widget-hometab-config-id');
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
        currentElement = $(this).parent().parent().parent();
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
})();