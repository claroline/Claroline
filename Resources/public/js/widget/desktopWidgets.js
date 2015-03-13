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
    var homeTabId = $('#widgets-hometab-datas-box').data('hometab-id');
    var homeTabType = $('#widgets-hometab-datas-box').data('hometab-type');

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
    
    $('#widgets-section').on('click', '#create-widget-instance', function () {
        window.Claroline.Modal.displayForm(
            Routing.generate('claro_desktop_widget_instance_create_form'),
            associateWidgetToHomeTab,
            function() {}
        );
    });
    
    var associateWidgetToHomeTab = function (widgetInstanceId) {
        
        if (homeTabType === 'desktop') {

            $.ajax({
                url: Routing.generate(
                    'claro_desktop_associate_widget_to_home_tab',
                    {
                        'homeTabId': homeTabId,
                        'widgetInstanceId': widgetInstanceId
                    }
                ),
                type: 'POST',
                success: function () {
                    window.location.reload();
                }
            });
        }
    }
})();