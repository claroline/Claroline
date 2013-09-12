(function () {
    'use strict';

    var homeTabId = $('#twig-home-tab-id').attr('data-home-tab-id');

    $('#widget-validate-button').click(function () {

        if ($('.chk-widget:checked').length > 0) {
            $('.chk-widget:checked').each(function (index, element) {
                var widgetId = element.value;

                $.ajax({
                    url: Routing.generate(
                        'claro_admin_associate_widget_to_home_tab',
                        {'homeTabId': homeTabId, 'widgetId': widgetId}
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