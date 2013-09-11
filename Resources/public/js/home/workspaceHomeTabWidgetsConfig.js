(function () {
    'use strict';

    var widgetHomeTabConfigId = -1;
    var workspaceId = $('#twig-workspace-id').attr('data-workspace-id');

    $('.widget-order-up').click(function () {
        widgetHomeTabConfigId = $(this).parent().parent().parent().attr('widget-config-id');
        $.ajax({
            url: Routing.generate(
                'claro_workspace_widget_home_tab_config_change_order',
                {
                    'widgetHomeTabConfigId': widgetHomeTabConfigId,
                    'workspaceId': workspaceId,
                    'direction': -1
                }
            ),
            type: 'POST',
            success: function () {
                location.reload();
            }
        });
    });

    $('.widget-order-down').click(function () {
        widgetHomeTabConfigId = $(this).parent().parent().parent().attr('widget-config-id');
        $.ajax({
            url: Routing.generate(
                'claro_workspace_widget_home_tab_config_change_order',
                {
                    'widgetHomeTabConfigId': widgetHomeTabConfigId,
                    'workspaceId': workspaceId,
                    'direction': 1
                }
            ),
            type: 'POST',
            success: function () {
                location.reload();
            }
        });
    });

    $('.whtc-visible-btn').click(function () {
        widgetHomeTabConfigId = $(this).parent().parent().parent().attr('widget-config-id');
        $.ajax({
            url: Routing.generate(
                'claro_workspace_widget_home_tab_config_change_visibility',
                {'widgetHomeTabConfigId': widgetHomeTabConfigId, 'workspaceId': workspaceId}
            ),
            type: 'POST',
            success: function () {
                location.reload();
            }
        });
    });

    $('.widget-remove').click(function () {
        widgetHomeTabConfigId = $(this).parent().parent().parent().attr('widget-config-id');
        $('#delete-widget-home-tab-validation-box').modal('show');
    });

    $('#delete-confirm-ok').click(function () {
        $.ajax({
            url: Routing.generate(
                'claro_workspace_widget_home_tab_config_delete',
                {'widgetHomeTabConfigId': widgetHomeTabConfigId, 'workspaceId': workspaceId}
            ),
            type: 'DELETE',
            success: function () {
                location.reload();
            }
        });
    });
})();