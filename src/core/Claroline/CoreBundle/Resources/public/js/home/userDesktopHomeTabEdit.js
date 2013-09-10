(function () {
    'use strict';

    var homeTabId = document.getElementById('twig-home-tab-id').getAttribute('data-home-tab-id');
    var homeTabOrder = document.getElementById('twig-home-tab-config-order').getAttribute('data-home-tab-config-order');

    $('#delete-home-tab-button').click(function () {
        $('#delete-home-tab-validation-box').modal('show');
    });

    $('#delete-confirm-ok').click(function () {
        $.ajax({
            url: Routing.generate(
                'claro_user_desktop_home_tab_delete',
                {'homeTabId': homeTabId, 'tabOrder': homeTabOrder}
            ),
            type: 'DELETE',
            success: function () {
                window.location = Routing.generate('claro_desktop_home_tab_properties');
            }
        });
    });
})();