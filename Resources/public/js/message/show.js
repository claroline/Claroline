(function () {
    'use strict';

    $('#message-users-btn').click(function () {
        $.ajax({
            url: Routing.generate(
                'claro_message_contactable_users'
            ),
            type: 'GET',
            success: function (datas) {
                $('#contacts-list').empty();
                $('#contacts-list').append(datas);
            }
        });
        $('#contacts-box').modal('show');
    });

    $('#users-nav-tab').on('click', function () {
        $('#groups-nav-tab').attr('class', '');
        $(this).attr('class', 'active');
    });

    $('#groups-nav-tab').on('click', function () {
        $('#users-nav-tab').attr('class', '');
        $(this).attr('class', 'active');
    });
})();