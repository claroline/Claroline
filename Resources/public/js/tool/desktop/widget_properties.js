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
            {'adminConfig': displayConfigId, 'widget': widgetId }
        );
        $.ajax({url: route, type: 'POST'});
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
            }
        });
    };
})();

