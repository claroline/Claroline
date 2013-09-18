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
    
   $('.form-name-widget').on('submit', function (e) {
        e.preventDefault();
        var formAction = $(e.currentTarget).attr('action');
        var form = e.currentTarget;
        var formData = new FormData(form);
        submitForm(formAction, formData);
   });
   
   var submitForm = function (formAction, formData) {
        $.ajax({
            url: formAction,
            data: formData,
            type: 'POST',
            processData: false,
            contentType: false,
            success: function () {
                //~ if 200 ~append form
            }
        });
    };
})();