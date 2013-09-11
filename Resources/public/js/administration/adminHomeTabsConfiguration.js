(function () {
    'use strict';

    $('.chk-visible').on('click', function () {
        var visible = ($(this).attr('checked') === 'checked') ? 'visible' : 'invisible';
        var homeTabConfigId = $(this).parent().parent().attr('data-id');

        $.ajax({
            url: Routing.generate(
                'claro_admin_home_tab_update_visibility',
                {'homeTabConfigId': homeTabConfigId, 'visible': visible}
            ),
            type: 'POST'
        });
    });

    $('.chk-admin-lock').click(function () {
        var locked = ($(this).attr('checked') === 'checked') ? 'locked' : 'unlocked';
        var homeTabConfigId = $(this).parent().parent().attr('data-id');

        $.ajax({
            url: Routing.generate(
                'claro_admin_home_tab_update_lock',
                {'homeTabConfigId': homeTabConfigId, 'locked': locked}
            ),
            type: 'POST'
        });
    });
})();