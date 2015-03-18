/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function () {
    'use strict';

    var currentHomeTabId = parseInt($('#hometab-datas-box').data('hometab-id'));
    var homeTabType = $('#hometab-datas-box').data('hometab-type');
    var currentWidgetInstanceId;
    
    $('#admin-home-content').on('click', '#add-hometab-btn', function () {
        window.Claroline.Modal.displayForm(
            Routing.generate(
                'claro_admin_home_tab_create_form',
                {'homeTabType' : homeTabType}
            ),
            openHomeTab,
            function() {}
        );
    });
    
    $('#admin-home-content').on('click', '.edit-hometab-btn', function (e) {
        e.preventDefault();
        var homeTabElement= $(this).parents('.hometab-element');
        var homeTabId = homeTabElement.data('hometab-id');
        var homeTabConfigId = homeTabElement.data('hometab-config-id');
        
        window.Claroline.Modal.displayForm(
            Routing.generate(
                'claro_admin_home_tab_edit_form',
                {
                    'homeTabType': homeTabType,
                    'homeTab': homeTabId,
                    'homeTabConfig': homeTabConfigId
                }
            ),
            renameHomeTab,
            function() {}
        );
    });

    $('#admin-home-content').on('click', '.delete-hometab-btn', function (e) {
        e.preventDefault();
        var homeTabElement = $(this).parents('.hometab-element');
        var homeTabId = homeTabElement.data('hometab-id');

        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'claro_admin_home_tab_delete',
                {'homeTabType': homeTabType, 'homeTab': homeTabId}
            ),
            removeHomeTab,
            homeTabId,
            Translator.trans('home_tab_delete_confirm_message', {}, 'platform'),
            Translator.trans('home_tab_delete_confirm_title', {}, 'platform')
        );
    });
    
    $('#admin-hometabs-list').sortable({
        items: '.movable-hometab',
        cursor: 'move'
    });

    $('#admin-hometabs-list').on('sortupdate', function (event, ui) {

        if (this === ui.item.parents('#admin-hometabs-list')[0]) {
            var hcId = $(ui.item).data('hometab-config-id');
            var nextHcId = -1;
            var nextElement = $(ui.item).next();
            
            if (nextElement !== undefined && nextElement.hasClass('movable-hometab')) {
                nextHcId = nextElement.data('hometab-config-id');
            }
            
            $.ajax({
                url: Routing.generate(
                    'claro_admin_home_tab_config_reorder',
                    {
                        'homeTabType': homeTabType,
                        'homeTabConfig': hcId,
                        'nextHomeTabConfigId': nextHcId
                    }
                ),
                type: 'POST'
            });
        }
    });
    
    $('.grid-stack').gridstack({
        width: 12,
        animate: true
    });
    
    var openHomeTab = function (homeTabId) {
        window.location = Routing.generate(
            'claro_admin_home_tabs_configuration',
            {'homeTabId': homeTabId, 'homeTabType': homeTabType}
        );
    };

    var renameHomeTab = function (datas) {
        var id = datas['id'];
        var name = datas['name'];
        var visibility = datas['visibility'];
        var lock = datas['lock'];
        $('#hometab-name-' + id).html(name);
        
        if (visibility === 'hidden') {
            $('#hometab-name-' + id).addClass('strike');
        } else {
            $('#hometab-name-' + id).removeClass('strike');
        }
        
        if (lock === 'locked') {
            $('#hometab-lock-' + id).removeClass('hidden');
        } else {
            $('#hometab-lock-' + id).addClass('hidden');
        }
    };
    
    var removeHomeTab = function (event, homeTabId) {
        
        if (currentHomeTabId === parseInt(homeTabId)) {
            window.location.reload();
        } else {
            $('#hometab-element-' + homeTabId).remove();
        }
    };
    
    var removeWidget = function (event, widgetHomeTabConfigId) {
        var widgetElement = $('#widget-element-' + widgetHomeTabConfigId);
        var grid = $('.grid-stack').data('gridstack');
        grid.remove_widget(widgetElement);
    };

    var reloadPage = function () {
        window.location.reload();
    };
    
    var updateWidget = function (datas) {
        var id = datas['id'];
        var color = (datas['color'] === null) ? '' : datas['color'];
        var visibility = datas['visibility'];
        $('#widget-element-title-' + id).html(datas['title']);
        $('#widget-element-header-' + id).css('background-color', color);
        $('#widget-element-content-' + id).css('border-color', color);
        
        if (visibility === 'hidden') {
            $('#widget-element-title-' + id).addClass('strike');
        } else {
            $('#widget-element-title-' + id).removeClass('strike');
        }
    };
//    var displayedHomeTabId = $('#hometab-id-div').attr('hometab-id');
//    var homeTabType = $('#hometab-type-div').attr('hometab-type-value');
//    var currentElement;
//    var currentHomeTabId;
//    var currentHomeTabConfigId;
//    var currentWidgetHomeTabConfigId;
//    var currentWidgetInstanceId;
//    
//    // Click on edit button of a hometab
//    $('.hometab-rename-btn').on('click', function (e) {
//        e.preventDefault();
//        e.stopPropagation();
//
//        currentElement = $(this).parents('.hometab-element');
//        currentHomeTabId = currentElement.attr('hometab-id');
//
//        $.ajax({
//            url: Routing.generate(
//                'claro_admin_home_tab_edit_form',
//                {'homeTabId': currentHomeTabId}
//            ),
//            type: 'GET',
//            success: function (datas) {
//                openFormModal(
//                    Translator.trans('home_tab_edition', {}, 'platform'),
//                    datas
//                );
//            }
//        });
//    });
//
//    // Click on OK button of the edit HomeTab form modal
//    $('body').on('click', '#form-edit-hometab-ok-btn', function (e) {
//        e.stopImmediatePropagation();
//        e.preventDefault();
//
//        var form = document.getElementById('edit-hometab-form');
//        var action = form.getAttribute('action');
//        var formData = new FormData(form);
//
//        $.ajax({
//            url: action,
//            data: formData,
//            type: 'POST',
//            processData: false,
//            contentType: false,
//            complete: function(jqXHR) {
//                switch (jqXHR.status) {
//                    case 204:
//                        closeFormModal();
//
//                        var route = (homeTabType === 'desktop') ?
//                            'claro_admin_desktop_home_tabs_configuration' :
//                            'claro_admin_workspace_home_tabs_configuration';
//                        window.location = Routing.generate(
//                            route,
//                            {'homeTabId': currentHomeTabId}
//                        );
//                        break;
//                    default:
//                        $('#form-modal-body').html(jqXHR.responseText);
//                }
//            }
//        });
//    });
//
//    // Click on CANCEL button of the Create/Rename HomeTab form modal
//    $('body').on('click', '#form-cancel-btn', function () {
//        closeFormModal();
//    });
//
//    // Click on visibility button of a hometab
//    $('.hometab-visibility-btn').on('click', function (e) {
//        e.preventDefault();
//        e.stopPropagation();
//
//        var visibilityBtn = $(this);
//        currentElement = visibilityBtn.parents('.hometab-element');
//        var homeTabConfigId = currentElement.attr('hometab-config-id');
//        var visible = (visibilityBtn.attr('visiblility-value')).trim();
//        var newVisible = (visible === 'visible') ? 'invisible' : 'visible';
//
//        $.ajax({
//            url: Routing.generate(
//                'claro_admin_home_tab_update_visibility',
//                {'homeTabConfigId': homeTabConfigId, 'visible': newVisible}
//            ),
//            type: 'POST',
//            success: function () {
//                if (newVisible === 'visible') {
//                    visibilityBtn.attr('visiblility-value', 'visible')
//                    visibilityBtn.removeClass('fa-eye-slash');
//                    visibilityBtn.addClass('fa-eye');
//                } else {
//                    visibilityBtn.attr('visiblility-value', 'invisible')
//                    visibilityBtn.removeClass('fa-eye');
//                    visibilityBtn.addClass('fa-eye-slash');
//                }
//            }
//        });
//    });
//
//    // Click on lock button of a hometab
//    $('.hometab-lock-btn').on('click', function (e) {
//        e.preventDefault();
//        e.stopPropagation();
//
//        var lockBtn = $(this);
//        currentElement = lockBtn.parents('.hometab-element');
//        var homeTabConfigId = currentElement.attr('hometab-config-id');
//        var locked = (lockBtn.attr('lock-value')).trim();
//        var newLocked = (locked === 'locked') ? 'unlocked' : 'locked';
//
//        $.ajax({
//            url: Routing.generate(
//                'claro_admin_home_tab_update_lock',
//                {'homeTabConfigId': homeTabConfigId, 'locked': newLocked}
//            ),
//            type: 'POST',
//            success: function () {
//                if (newLocked === 'locked') {
//                    lockBtn.attr('lock-value', 'locked')
//                    lockBtn.removeClass('fa-unlock');
//                    lockBtn.addClass('fa-lock');
//                } else {
//                    lockBtn.attr('lock-value', 'unlocked')
//                    lockBtn.removeClass('fa-lock');
//                    lockBtn.addClass('fa-unlock');
//                }
//            }
//        });
//    });
//
//    // Click on left reorder button of a hometab
//    $('.hometab-reorder-left-btn').on('click', function (e) {
//        e.preventDefault();
//        e.stopPropagation();
//
//        currentElement = $(this).parents('.hometab-element');
//        var homeTabConfigId = currentElement.attr('hometab-config-id');
//
//        $.ajax({
//            url: Routing.generate(
//                'claro_admin_home_tab_config_change_order',
//                {'homeTabConfigId': homeTabConfigId, 'direction': -1}
//            ),
//            type: 'POST',
//            success: function (data) {
//                if (data === '-1') {
//                    var previousSibling = currentElement.prev();
//                    previousSibling.before(currentElement);
//                }
//            }
//        });
//    });
//
//    // Click on right reorder button of a hometab
//    $('.hometab-reorder-right-btn').on('click', function (e) {
//        e.preventDefault();
//        e.stopPropagation();
//
//        currentElement = $(this).parents('.hometab-element');
//        var homeTabConfigId = currentElement.attr('hometab-config-id');
//
//        $.ajax({
//            url: Routing.generate(
//                'claro_admin_home_tab_config_change_order',
//                {'homeTabConfigId': homeTabConfigId, 'direction': 1}
//            ),
//            type: 'POST',
//            success: function (data) {
//                if (data === '1') {
//                    var nextSibling = currentElement.next();
//                    nextSibling.after(currentElement);
//                }
//            }
//        });
//    });
//
//    // Click on delete button of a hometab
//    $('.hometab-delete-btn').on('click', function (e) {
//        e.preventDefault();
//        e.stopPropagation();
//
//        currentElement = $(this).parents('.hometab-element');
//        currentHomeTabId = currentElement.attr('hometab-id');
//        currentHomeTabConfigId = currentElement.attr('hometab-config-id');
//        $('#delete-hometab-validation-box').modal('show');
//    });
//
//    // Click on OK button of hometab delete confirmation modal
//    $('#delete-hometab-confirm-ok').click(function () {
//        $.ajax({
//            url: Routing.generate(
//                'claro_admin_home_tab_config_delete',
//                {'homeTabConfigId': currentHomeTabConfigId}
//            ),
//            type: 'DELETE',
//            success: function () {
//                $('#delete-hometab-validation-box').modal('hide');
//
//                if (displayedHomeTabId === currentHomeTabId) {
//                    var route = (homeTabType === 'desktop') ?
//                        'claro_admin_desktop_home_tabs_configuration' :
//                        'claro_admin_workspace_home_tabs_configuration';
//                    window.location = Routing.generate(
//                        route,
//                        {'homeTabId': -1}
//                    );
//                } else {
//                    currentElement.remove();
//                }
//            }
//        });
//    });
//
//    // Click on widget create button
//    $('.add-widget-instance').on('click', function () {
//        $.ajax({
//            url: Routing.generate(
//                'claro_admin_widget_instance_create_form',
//                {'homeTabType': homeTabType}
//            ),
//            type: 'GET',
//            success: function (datas) {
//                openFormModal(
//                    Translator.trans('create_widget_instance', {}, 'platform'),
//                    datas
//                );
//            }
//        });
//    });
//
//    // Click on OK button of the Create Widget instance form modal
//    $('body').on('click', '#form-create-widget-instance-ok-btn', function (e) {
//        e.stopImmediatePropagation();
//        e.preventDefault();
//
//        var form = document.getElementById('create-widget-instance-form');
//        var action = form.getAttribute('action');
//        var formData = new FormData(form);
//
//        $.ajax({
//            url: action,
//            data: formData,
//            type: 'POST',
//            processData: false,
//            contentType: false,
//            success: function(datas, textStatus, jqXHR) {
//                switch (jqXHR.status) {
//                    case 201:
//                        var widgetInstanceId = parseInt(datas);
//
//                        $.ajax({
//                            url: Routing.generate(
//                                'claro_admin_associate_widget_to_home_tab',
//                                {
//                                    'homeTabId': displayedHomeTabId,
//                                    'widgetInstanceId': widgetInstanceId
//                                }
//                            ),
//                            type: 'POST',
//                            success: function () {
//                                closeFormModal();
//                                window.location.reload();
//                            }
//                        });
//                        break;
//                    default:
//                        $('#form-modal-body').html(jqXHR.responseText);
//                }
//            }
//        });
//    });
//
//    // Click on delete button of a widget
//    $('.widget-delete-btn').click(function () {
//        currentElement = $(this).parents('.widget-instance-panel');
//        currentWidgetHomeTabConfigId = currentElement.attr('widget-hometab-config-id');
//        $('#delete-widget-hometab-validation-box').modal('show');
//    });
//
//    // Click on OK button of widget delete confirmation modal
//    $('#delete-widget-hometab-confirm-ok').click(function () {
//        $.ajax({
//            url: Routing.generate(
//                'claro_admin_widget_home_tab_config_delete',
//                {'widgetHomeTabConfigId': currentWidgetHomeTabConfigId}
//            ),
//            type: 'DELETE',
//            success: function () {
//                window.location.reload();
//            }
//        });
//    });
//
//    // Click on the widget rename button
//    $('.widget-instance-rename').on('click', function () {
//        currentElement = $(this).parents('.widget-instance-panel');
//        currentWidgetInstanceId = currentElement.attr('widget-instance-id');
//
//        $.ajax({
//            url: Routing.generate(
//                'claro_admin_widget_instance_name_edit_form',
//                {'widgetInstanceId': currentWidgetInstanceId}
//            ),
//            type: 'GET',
//            success: function (datas) {
//                openFormModal(
//                    Translator.trans('rename_widget_instance', {}, 'platform'),
//                    datas
//                );
//            }
//        });
//    });
//
//    // Click on OK button of the Rename Widget form modal
//    $('body').on('click', '#form-edit-widget-instance-ok-btn', function (e) {
//        e.stopImmediatePropagation();
//        e.preventDefault();
//
//        var form = document.getElementById('edit-widget-instance-form');
//        var action = form.getAttribute('action');
//        var formData = new FormData(form);
//
//        $.ajax({
//            url: action,
//            data: formData,
//            type: 'POST',
//            processData: false,
//            contentType: false,
//            complete: function(jqXHR) {
//                switch (jqXHR.status) {
//                    case 204:
//                        var value = $('#widget_display_form_name').val();
//                        currentElement.find('.widget-instance-name').html(value);
//                        closeFormModal();
//                        break;
//                    default:
//                        $('#form-modal-body').html(jqXHR.responseText);
//                }
//            }
//        });
//    });
//
//    // Click on widget order-up button
//    $('#widgets-list-panel').on('click', '.widget-order-up', function () {
//        currentElement = $(this).parents('.widget-instance-panel');
//        currentWidgetHomeTabConfigId = currentElement.attr('widget-hometab-config-id');
//
//        $.ajax({
//            url: Routing.generate(
//                'claro_admin_widget_home_tab_config_change_order',
//                {
//                    'widgetHomeTabConfigId': currentWidgetHomeTabConfigId,
//                    'direction': -1
//                }
//            ),
//            type: 'POST',
//            success: function (data) {
//                if (data === '-1') {
//                    var previousSibling = currentElement.prev();
//                    previousSibling.before(currentElement);
//                    var currentOrderBtns = currentElement.find('.widget-order-btn-group');
//                    var previousOrderBtns = previousSibling.find('.widget-order-btn-group');
//                    var currentHtml = currentOrderBtns.html();
//                    var previousHtml = previousOrderBtns.html();
//                    currentOrderBtns.html(previousHtml);
//                    previousOrderBtns.html(currentHtml);
//                }
//            }
//        });
//    });
//
//    // Click on widget order-down button
//    $('#widgets-list-panel').on('click', '.widget-order-down', function () {
//        currentElement = $(this).parents('.widget-instance-panel');
//        currentWidgetHomeTabConfigId = currentElement.attr('widget-hometab-config-id');
//
//        $.ajax({
//            url: Routing.generate(
//                'claro_admin_widget_home_tab_config_change_order',
//                {
//                    'widgetHomeTabConfigId': currentWidgetHomeTabConfigId,
//                    'direction': 1
//                }
//            ),
//            type: 'POST',
//            success: function (data) {
//                if (data === '1') {
//                    var nextSibling = currentElement.next();
//                    nextSibling.after(currentElement);
//                    var currentOrderBtns = currentElement.find('.widget-order-btn-group');
//                    var nextOrderBtns = nextSibling.find('.widget-order-btn-group');
//                    var currentHtml = currentOrderBtns.html();
//                    var nextHtml = nextOrderBtns.html();
//                    currentOrderBtns.html(nextHtml);
//                    nextOrderBtns.html(currentHtml);
//                }
//            }
//        });
//    });
//
//    // Click on widget visibility button
//    $('.widget-visibility-btn').on('click', function () {
//        var visibilityBtn = $(this);
//        currentElement = visibilityBtn.parents('.widget-instance-panel');
//        currentWidgetHomeTabConfigId = currentElement.attr('widget-hometab-config-id');
//        var visible = (visibilityBtn.attr('visiblility-value')).trim();
//        var newVisible = (visible === 'visible') ? 'invisible' : 'visible';
//
//        $.ajax({
//            url: Routing.generate(
//                'claro_admin_widget_home_tab_config_change_visibility',
//                {'widgetHomeTabConfigId': currentWidgetHomeTabConfigId}
//            ),
//            type: 'POST',
//            success: function () {
//                if (newVisible === 'visible') {
//                    visibilityBtn.attr('visiblility-value', 'visible')
//                    visibilityBtn.removeClass('fa-eye-slash');
//                    visibilityBtn.addClass('fa-eye');
//                } else {
//                    visibilityBtn.attr('visiblility-value', 'invisible')
//                    visibilityBtn.removeClass('fa-eye');
//                    visibilityBtn.addClass('fa-eye-slash');
//                }
//            }
//        });
//    });
//
//    // Click on widget lock button
//    $('.widget-lock-btn').on('click', function () {
//        var lockBtn = $(this);
//        currentElement = lockBtn.parents('.widget-instance-panel');
//        currentWidgetHomeTabConfigId = currentElement.attr('widget-hometab-config-id');
//        var locked = (lockBtn.attr('lock-value')).trim();
//        var newLocked = (locked === 'locked') ? 'unlocked' : 'locked';
//
//        $.ajax({
//            url: Routing.generate(
//                'claro_admin_widget_home_tab_config_change_lock',
//                {'widgetHomeTabConfigId': currentWidgetHomeTabConfigId}
//            ),
//            type: 'POST',
//            success: function () {
//                if (newLocked === 'locked') {
//                    lockBtn.attr('lock-value', 'locked')
//                    lockBtn.removeClass('fa-unlock');
//                    lockBtn.addClass('fa-lock');
//                } else {
//                    lockBtn.attr('lock-value', 'unlocked')
//                    lockBtn.removeClass('fa-lock');
//                    lockBtn.addClass('fa-unlock');
//                }
//            }
//        });
//    });
//
//    // Click on widget configuration button
//    $('.widget-instance-config').on('click', function () {
//        var configButton = $(this);
//        currentElement = configButton.parents('.widget-instance-panel');
//        currentWidgetInstanceId = currentElement.attr('widget-instance-id');
//
//        $.ajax({
//            url: Routing.generate(
//                'claro_admin_widget_configuration',
//                {'widgetInstance': currentWidgetInstanceId}
//            ),
//            type: 'GET',
//            success: function (datas) {
//                configButton.addClass('hide');
//                var widgetEditionElement = currentElement.find('.widget-instance-edition-element');
//                widgetEditionElement.html(datas);
//                widgetEditionElement.removeClass('hide');
//                var textArea = $('textarea', datas)[0];
//            }
//        });
//    });
//
//    // Click on CANCEL button of the configuration Widget form
//    $('.widget-instance-edition-element').on(
//        'click',
//        '.claro-widget-form-cancel',
//        function (e) {
//            e.stopImmediatePropagation();
//            e.preventDefault();
//            var widgetElement = $(this).parents('.widget-instance-panel');
//            var widgetEditionElement = widgetElement.find('.widget-instance-edition-element');
//            widgetEditionElement.addClass('hide');
//            widgetEditionElement.empty();
//            var configButton = widgetElement.find('.widget-instance-config');
//            configButton.removeClass('hide');
//        }
//    );
//
//    // Click on OK button of the configuration Widget form
//    $('#widgets-list-panel').on(
//        'submit',
//        '.widget-instance-edition-element > form',
//        function(e) {
//            e.stopImmediatePropagation();
//            e.preventDefault();
//
//            var form = e.currentTarget;
//            var action = $(e.currentTarget).attr('action');
//            var formData = new FormData(form);
//
//            var widgetElement = $(this).parents('.widget-instance-panel');
//            var configButton = widgetElement.find('.widget-instance-config');
//            var widgetEditionElement = widgetElement.find('.widget-instance-edition-element');
//
//            $.ajax({
//                url: action,
//                data: formData,
//                type: 'POST',
//                processData: false,
//                contentType: false,
//                complete: function(jqXHR) {
//                    switch (jqXHR.status) {
//                        case 204:
//                            widgetEditionElement.addClass('hide');
//                            widgetEditionElement.empty();
//                            configButton.removeClass('hide');
//                            break;
//                        default:
//                            widgetEditionElement.html(jqXHR.responseText);
//                    }
//                }
//            });
//        }
//    );
})();
