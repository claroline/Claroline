(function () {
    'use strict';

    $('#all-my-workspaces-btn').on('click', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();

        var active = $(this).hasClass('active');

        if (!active) {
            $.ajax({
                url: Routing.generate('claro_display_workspaces_widget', {'mode': 0}),
                type: 'GET',
                success: function (datas) {
                    $('#workspaces-list-element').html(datas);
                    $('#favourite-workspaces-btn').removeClass('active');
                    $('#all-my-workspaces-btn').addClass('active');
                }
            });
        }
    });

    $('#favourite-workspaces-btn').on('click', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();

        var active = $(this).hasClass('active');

        if (!active) {
            $.ajax({
                url: Routing.generate('claro_display_workspaces_widget', {'mode': 1}),
                type: 'GET',
                success: function (datas) {
                    $('#workspaces-list-element').html(datas);
                    $('#all-my-workspaces-btn').removeClass('active');
                    $('#favourite-workspaces-btn').addClass('active');
                }
            });
        }
    });
})();