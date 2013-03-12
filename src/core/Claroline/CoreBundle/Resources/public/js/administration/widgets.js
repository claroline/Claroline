(function () {
    'use strict';

    var stackedRequests = 0;

    $('.chk-admin-lock').on('change', function (e) {
        var id = e.currentTarget.parentNode.parentNode.dataset.id;
        var route = Routing.generate('claro_admin_invert_widgetconfig_lock', {'displayConfigId': id});
        stackedRequests++;
        $('.please-wait').show();
        $.ajax({
            url: route,
            type: 'POST',
            success: function () {
                stackedRequests--;
                if (stackedRequests === 0) {
                    $('.please-wait').hide();
                }
            }
        });
    });

    $('.chk-config-visible').on('change', function (e) {
        var id = e.currentTarget.parentNode.parentNode.dataset.id;
        var route = Routing.generate('claro_admin_invert_widgetconfig_visible', {'displayConfigId': id});
        stackedRequests++;
        $('.please-wait').show();
        $.ajax({
            url: route,
            type: 'POST',
            success: function () {
                stackedRequests--;
                if (stackedRequests === 0) {
                    $('.please-wait').hide();
                }
            }
        });
    });
})();