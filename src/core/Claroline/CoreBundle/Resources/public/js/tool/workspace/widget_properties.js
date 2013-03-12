(function () {
    'use strict';
    var stackedRequests = 0;
    $.ajaxSetup({
        beforeSend: function () {
            stackedRequests++;
            $('.please-wait').show();
        },
        complete: function () {
            stackedRequests--;
            if (stackedRequests === 0) {
                $('.please-wait').hide();
            }
        }
    });

    $('.chk-config-visible').on('change', function (e) {
        var displayConfigId = e.currentTarget.parentNode.parentNode.dataset.id;
        var widgetId = e.currentTarget.parentNode.parentNode.dataset.widgetId;
        var workspaceId = e.currentTarget.parentNode.parentNode.parentElement.dataset.workspaceId;
        var route = Routing.generate('claro_workspace_widget_invertvisible',
            {'displayConfigId': displayConfigId, 'widgetId': widgetId, 'workspaceId': workspaceId}
        );
        $.ajax({url: route, type: 'POST'});
    });

})();

