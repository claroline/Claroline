/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function () {
    'use strict';

    function menuHighlightMode()
    {
        var mode = $('#mode-element').data('mode');
        
        if (mode === 1) {
            $('#all-my-workspaces-btn').removeClass('active');
            $('#favourite-workspaces-btn').addClass('active');
        } else {
            $('#favourite-workspaces-btn').removeClass('active');
            $('#all-my-workspaces-btn').addClass('active');
        }
    }

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
    
    $(document).ready(function () {
        menuHighlightMode();
    });
})();