(function () {
    'use strict';

    var currentElement;
    var currentWidgetHomeTabConfigId;
    var homeTabType = $('#hometab-type-div').attr('hometab-type-value');

    if (homeTabType === 'workspace') {
        var workspaceId = $('#workspace-id-div').attr('workspace-id');
    }

    $('.widget-delete-btn').click(function () {
        currentElement = $(this).parent().parent().parent().parent();
        currentWidgetHomeTabConfigId = $(this).parent().attr('widget-hometab-config-id');
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
        currentElement = visibilityBtn.parent().parent().parent().parent();
        currentWidgetHomeTabConfigId = visibilityBtn.parent().attr('widget-hometab-config-id');
        var visible = visibilityBtn.attr('visiblility-value');
        var newVisible = (visible === 'visible') ? 'invisible' : 'visible';
        var route;

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
                    visibilityBtn.attr('visiblility-value', 'visible')
                    visibilityBtn.removeClass('icon-eye-close');
                    visibilityBtn.addClass('icon-eye-open');
                    visibilityBtn.parent().parent().parent().parent().removeClass('toggle-visible');
                } else {
                    visibilityBtn.attr('visiblility-value', 'invisible')
                    visibilityBtn.removeClass('icon-eye-open');
                    visibilityBtn.addClass('icon-eye-close');
                    visibilityBtn.parent().parent().parent().parent().addClass('toggle-visible');
                }
            }
        });
    });

    $('#widgets-list-panel').on('click', '.widget-order-up', function () {
        currentElement = $(this).parent().parent().parent().parent().parent();
        currentWidgetHomeTabConfigId = $(this).parent().parent().attr('widget-hometab-config-id');
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

    $('#widgets-list-panel').on('click', '.widget-order-down', function () {
        currentElement = $(this).parent().parent().parent().parent().parent();
        currentWidgetHomeTabConfigId = $(this).parent().parent().attr('widget-hometab-config-id');
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
})();