(function () {
    'use strict';

    var homeTabId = document.getElementById('twig-home-tab-id').getAttribute('data-home-tab-id');

    $('#delete-home-tab-button').click(function () {
        $('#delete-home-tab-validation-box').modal('show');
    });

    $('#delete-confirm-ok').click(function () {
        $.ajax({
            url: Routing.generate('claro_admin_home_tab_delete', {'homeTabId': homeTabId}),
            type: 'DELETE',
            success: function () {
                window.location = Routing.generate('claro_admin_home_tabs_configuration');
            }
        });
    });
})();