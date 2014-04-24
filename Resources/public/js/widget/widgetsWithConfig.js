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

    var currentElement;
    var currentWidgetHomeTabConfigId;
    var currentWidgetInstanceId;
    var displayedHomeTabId = $('#hometab-id-div').attr('hometab-id');
    var homeTabType = $('#hometab-type-div').attr('hometab-type-value');

    function inverseBtns(current, previous)
    {
        var currentBtnUp = current.find('.widget-order-up').parent();
        var currentBtnDown = current.find('.widget-order-down').parent();
        var previousBtnUp = previous.find('.widget-order-up').parent();
        var previousBtnDown = previous.find('.widget-order-down').parent();
        if (previousBtnUp) {
            current.find('.widget-instance-menu').append(previousBtnUp.wrap('<p/>').parent().html());
        }
        if (previousBtnDown) {
            current.find('.widget-instance-menu').append(previousBtnDown.wrap('<p/>').parent().html());
        }
        if (currentBtnUp) {
            previous.find('.widget-instance-menu').append(currentBtnUp.wrap('<p/>').parent().html());
        }
        if (currentBtnDown) {
            previous.find('.widget-instance-menu').append(currentBtnDown.wrap('<p/>').parent().html());
        }
        currentBtnUp.remove();
        currentBtnDown.remove();
        previousBtnUp.remove();
        previousBtnDown.remove();
    }

    if (homeTabType === 'workspace') {
        var workspaceId = $('#workspace-id-div').attr('workspace-id');
    }

    function openWidgetModal(content)
    {
        $('#widget-modal-body').html(content);
        $('#widget-modal-box').modal('show');
    }

    function closeWidgetModal()
    {
        $('#widget-modal-box').modal('hide');
        $('#widget-modal-body').empty();
    }

    function openWidgetCreationModal(content)
    {
        $('#create-widget-instance-modal-body').html(content);
        $('#create-widget-instance-modal-box').modal('show');
    }

    function closeWidgetCreationModal()
    {
        $('#create-widget-instance-modal-box').modal('hide');
        $('#create-widget-instance-modal-body').empty();
    }

    $('.widget-delete-btn').click(function () {
        currentElement = $(this).parents('.widget-instance-panel');
        currentWidgetHomeTabConfigId = currentElement.attr('widget-hometab-config-id');
        $('#delete-widget-hometab-validation-box').modal('show');
    });

    // Click on OK button of delete confirmation modal
    $('#delete-widget-hometab-confirm-ok').click(function () {
        var route;

        if (homeTabType === 'desktop') {
            route = Routing.generate(
                'claro_desktop_widget_home_tab_config_delete',
                {'widgetHomeTabConfigId': currentWidgetHomeTabConfigId}
            );
        } else {
            route = Routing.generate(
                'claro_workspace_widget_home_tab_config_delete',
                {
                    'widgetHomeTabConfigId': currentWidgetHomeTabConfigId,
                    'workspaceId': workspaceId
                }
            );
        }

        $.ajax({
            url: route,
            type: 'DELETE',
            success: function () {
                currentElement.remove();
                $('#delete-widget-hometab-validation-box').modal('hide');
            }
        });
    });

    $('.widget-visibility-btn').on('click', function () {
        var visibilityBtn = $(this);
        currentElement = visibilityBtn.parents('.widget-instance-panel');
        currentWidgetHomeTabConfigId = currentElement.attr('widget-hometab-config-id');
        var visible = visibilityBtn.attr('visiblility-value');
        var newVisible = (visible === 'visible') ? 'invisible' : 'visible';
        var route;
        var translator = window.Translator;

        if (homeTabType === 'desktop') {
            route = Routing.generate(
                'claro_desktop_widget_home_tab_config_change_visibility',
                {'widgetHomeTabConfigId': currentWidgetHomeTabConfigId}
            );
        } else {
            route = Routing.generate(
                'claro_workspace_widget_home_tab_config_change_visibility',
                {
                    'widgetHomeTabConfigId': currentWidgetHomeTabConfigId,
                    'workspaceId': workspaceId
                }
            );
        }

        $.ajax({
            url: route,
            type: 'POST',
            success: function () {
                if (newVisible === 'visible') {
                    visibilityBtn.attr('visiblility-value', 'visible');
                    visibilityBtn.find('i').removeClass('icon-eye-open');
                    visibilityBtn.find('i').addClass('icon-eye-close');
                    visibilityBtn.find('span').html(translator.get('platform:hide'));
                    currentElement.find('.panel-title').first().removeClass('strike');
                    
                    currentElement.find('.panel-body').first().show('slow');
                } else {
                    visibilityBtn.attr('visiblility-value', 'invisible');
                    visibilityBtn.find('i').removeClass('icon-eye-close');
                    visibilityBtn.find('i').addClass('icon-eye-open');
                    visibilityBtn.find('span').html(translator.get('platform:display'));
                    currentElement.find('.panel-title').first().addClass('strike');
                    
                    currentElement.find('.panel-body').first().hide('slow');
                    
                }
            }
        });
    });

    $('#widgets-list-panel').on('click', '.widget-order-up', function () {
        currentElement = $(this).parents('.widget-instance-panel');
        currentWidgetHomeTabConfigId = currentElement.attr('widget-hometab-config-id');
        var route;

        if (homeTabType === 'desktop') {
            route = Routing.generate(
                'claro_desktop_widget_home_tab_config_change_order',
                {
                    'widgetHomeTabConfigId': currentWidgetHomeTabConfigId,
                    'direction': -1
                }
            );
        } else {
            route = Routing.generate(
                'claro_workspace_widget_home_tab_config_change_order',
                {
                    'widgetHomeTabConfigId': currentWidgetHomeTabConfigId,
                    'workspaceId': workspaceId,
                    'direction': -1
                }
            );
        }

        $.ajax({
            url: route,
            type: 'POST',
            success: function (status) {
                if (status === '-1') {
                    var previousSibling = currentElement.prev();
                    previousSibling.before(currentElement);

                    inverseBtns(currentElement, previousSibling);
                }
            }
        });
    });

    $('#widgets-list-panel').on('click', '.widget-order-down', function () {
        currentElement = $(this).parents('.widget-instance-panel');
        currentWidgetHomeTabConfigId = currentElement.attr('widget-hometab-config-id');
        var route;

        if (homeTabType === 'desktop') {
            route = Routing.generate(
                'claro_desktop_widget_home_tab_config_change_order',
                {
                    'widgetHomeTabConfigId': currentWidgetHomeTabConfigId,
                    'direction': 1
                }
            );
        } else {
            route = Routing.generate(
                'claro_workspace_widget_home_tab_config_change_order',
                {
                    'widgetHomeTabConfigId': currentWidgetHomeTabConfigId,
                    'workspaceId': workspaceId,
                    'direction': 1
                }
            );
        }

        $.ajax({
            url: route,
            type: 'POST',
            success: function (status) {
                if (status === '1') {
                    var nextSibling = currentElement.next();
                    nextSibling.after(currentElement);

                    inverseBtns(currentElement, nextSibling);
                }
            }
        });
    });

    $('.widget-instance-rename').on('click', function () {
        currentElement = $(this).parents('.widget-instance-panel');
        currentWidgetInstanceId = currentElement.attr('widget-instance-id');
        var route;

        if (homeTabType === 'desktop') {
            route = Routing.generate(
                'claro_desktop_widget_name_edit_form',
                {'widgetInstanceId': currentWidgetInstanceId}
            );
        } else {
            route = Routing.generate(
                'claro_workspace_widget_name_edit_form',
                {
                    'widgetInstanceId': currentWidgetInstanceId,
                    'workspaceId': workspaceId
                }
            );
        }

        $.ajax({
            url: route,
            type: 'GET',
            success: function (datas) {
                openWidgetModal(datas);
            }
        });
    });

    // Click on OK button of the Rename Widget form modal
    $('body').on('click', '#form-widget-ok-btn', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();

        var form = document.getElementById('widget-instance-form');
        var action = form.getAttribute('action');
        var formData = new FormData(form);

        $.ajax({
            url: action,
            data: formData,
            type: 'POST',
            processData: false,
            contentType: false,
            complete: function (jqXHR) {
                switch (jqXHR.status) {
                    case 204:
                        var value = $('#widget_display_form_name').val();
                        currentElement.find('.widget-instance-name').html(value);
                        closeWidgetModal();
                        break;
                    default:
                        $('#widget-modal-body').html(jqXHR.responseText);
                }
            }
        });
    });

    // Click on CANCEL button of the Rename Widget form modal
    $('body').on('click', '#form-widget-cancel-btn', function () {
        closeWidgetModal();
    });

    $('.widget-instance-config').on('click', function () {
        var configButton = $(this);
        currentElement = configButton.parents('.widget-instance-panel');
        currentWidgetInstanceId = currentElement.attr('widget-instance-id');
        var route;

        if (homeTabType === 'desktop') {
            route = Routing.generate(
                'claro_desktop_widget_configuration',
                {'widgetInstance': currentWidgetInstanceId}
            );
        } else {
            route = Routing.generate(
                'claro_workspace_widget_configuration',
                {
                    'widgetInstance': currentWidgetInstanceId,
                    'workspaceId': workspaceId
                }
            );
        }

        $.ajax({
            url: route,
            type: 'GET',
            success: function (datas) {
                configButton.addClass('hide');
                var widgetViewElement = currentElement.find('.widget-instance-view');
                var widgetEditionElement = currentElement.find('.widget-instance-edition');
                widgetViewElement.addClass('hide');
                widgetEditionElement.html(datas);
                widgetEditionElement.removeClass('hide');
            }
        });
    });

    // Click on OK button of the configuration Widget form
    $('#widgets-list-panel').on(
        'submit',
        '.widget-instance-edition > form',
        function (e) {
            e.stopImmediatePropagation();
            e.preventDefault();

            var form = e.currentTarget;
            var action = $(e.currentTarget).attr('action');
            var formData = new FormData(form);
            var widgetElement = $(this).parents('.widget-instance-panel');
            var widgetInstanceId = widgetElement.attr('widget-instance-id');
            var configButton = widgetElement.find('.widget-instance-config');
            var widgetViewElement = widgetElement.find('.widget-instance-view');
            var widgetEditionElement = widgetElement.find('.widget-instance-edition');

            $.ajax({
                url: action,
                data: formData,
                type: 'POST',
                processData: false,
                contentType: false,
                complete: function (jqXHR) {
                    switch (jqXHR.status) {
                        case 204:
                            $.ajax({
                                url: Routing.generate(
                                    'claro_widget_content',
                                    {'widgetInstanceId': widgetInstanceId}
                                ),
                                type: 'GET',
                                success: function (datas) {
                                    widgetEditionElement.addClass('hide');
                                    widgetEditionElement.empty();
                                    widgetViewElement.html(datas);
                                    widgetViewElement.removeClass('hide');
                                    configButton.removeClass('hide');
                                }
                            });
                            break;
                        default:
                            widgetEditionElement.html(jqXHR.responseText);
                    }
                }
            });
        }
    );

    // Click on CANCEL button of the configuration Widget form
    $('#widgets-list-panel').on(
        'click',
        '.claro-widget-form-cancel',
        function (e) {
            e.stopImmediatePropagation();
            e.preventDefault();
            var widgetElement = $(this).parents('.widget-instance-panel');
            var configButton = widgetElement.find('.widget-instance-config');
            var widgetViewElement = widgetElement.find('.widget-instance-view');
            var widgetEditionElement = widgetElement.find('.widget-instance-edition');
            widgetEditionElement.addClass('hide');
            widgetEditionElement.empty();
            widgetViewElement.removeClass('hide');
            configButton.removeClass('hide');
        }
    );

    $('.add-widget-instance').on('click', function () {
        var route;

        if (homeTabType === 'desktop') {
            route = Routing.generate('claro_desktop_widget_instance_create_form');
        } else {
            route = Routing.generate(
                'claro_workspace_widget_instance_create_form',
                {'workspaceId': workspaceId}
            );
        }

        $.ajax({
            url: route,
            type: 'GET',
            success: function (datas) {
                openWidgetCreationModal(datas);
            }
        });
    });

    // Click on CANCEL button of the Create Widget instance form modal
    $('body').on('click', '#form-widget-instance-cancel-btn', function () {
        closeWidgetCreationModal();
    });

    // Click on OK button of the Create Widget instance form modal
    $('body').on('click', '#form-widget-instance-ok-btn', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();

        var form = document.getElementById('widget-instance-form');
        var action = form.getAttribute('action');
        var formData = new FormData(form);

        $.ajax({
            url: action,
            data: formData,
            type: 'POST',
            processData: false,
            contentType: false,
            success: function (datas, textStatus, jqXHR) {
                switch (jqXHR.status) {
                    case 201:
                        var route;
                        var widgetInstanceId = parseInt(datas);

                        if (homeTabType === 'desktop') {
                            route = Routing.generate(
                                'claro_desktop_associate_widget_to_home_tab',
                                {
                                    'homeTabId': displayedHomeTabId,
                                    'widgetInstanceId': widgetInstanceId
                                }
                            );
                        } else {
                            route = Routing.generate(
                                'claro_workspace_associate_widget_to_home_tab',
                                {
                                    'homeTabId': displayedHomeTabId,
                                    'widgetInstanceId': widgetInstanceId,
                                    'workspaceId': workspaceId
                                }
                            );
                        }

                        $.ajax({
                            url: route,
                            type: 'POST',
                            success: function () {
                                closeWidgetCreationModal();
                                window.location.reload();
                            }
                        });
                        break;
                    default:
                        $('#create-widget-instance-modal-body').html(jqXHR.responseText);
                }
            }
        });
    });
})();
