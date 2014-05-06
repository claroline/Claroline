(function () {
    'use strict';

    var translator = window.Translator;

    $('#check-notifications').on('change', function () {
        var val = ($('#check-notifications').is(':checked')) ? 1: 0;
        $.ajax({
            type: 'POST',
            url: Routing.generate('claro_message_notification', {'isNotified': val}),
            success: function () {
                var translationKey = (val === 0) ? 'notification_deactivated': 'notification_activated';
                var toAppend = '<div class="alert alert-info">' +
                    '<a class="close" data-dismiss="alert" href="#">×</a>' +
                    translator.get('platform' + ':' + translationKey) +
                    '</div>';
                $('#flashbox').html(toAppend);
            }
        });
    });
})();
