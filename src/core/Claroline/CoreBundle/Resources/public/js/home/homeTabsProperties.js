(function () {
    'use strict';

    $('.chk-visible').on('click', function () {
        var visible = ($(this).attr('checked') === 'checked') ? 'visible' : 'invisible';
        var homeTabConfigId = $(this).parent().parent().attr('data-id');

        $.ajax({
            url: Routing.generate(
                'claro_home_tab_update_visibility',
                {'homeTabConfigId': homeTabConfigId, 'visible': visible}
            ),
            type: 'POST'
        });
    });
})();