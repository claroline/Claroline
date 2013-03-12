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
        var route = Routing.generate('claro_desktop_widget_invertvisible',
            {'displayConfigId': displayConfigId, 'widgetId': widgetId }
        );
        $.ajax({url: route, type: 'POST'});
    });

})();

