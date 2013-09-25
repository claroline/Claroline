(function () {
    'use strict';

    var homeTabId = $('#twig-home-tab-id').attr('data-home-tab-id');
    var workspaceId = $('#twig-workspace-id').attr('data-workspace-id');

    $('#widget-validate-button').click(function () {

        if ($('.chk-widget-instance:checked').length > 0) {
            $('.chk-widget-instance:checked').each(function (index, element) {
                var widgetInstanceId = element.value;

                $.ajax({
                    url: Routing.generate(
                        'claro_workspace_associate_widget_to_home_tab',
                        {
                            'homeTabId': homeTabId,
                            'widgetInstanceId': widgetInstanceId,
                            'workspaceId': workspaceId
                        }
                    ),
                    type: 'POST',
                    success: function () {
                        $(element).parent().parent().remove();
                    }
                });
            });
        }
    });
})();