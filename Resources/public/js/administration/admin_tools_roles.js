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

    $('.chk-role').on('click', function(event) {
        var route;
        var roleId = $(event.target).attr('data-role-id');
        var toolId = $(event.target).attr('data-tool-id');
        route = (!$(event.target).is(':checked')) ?
            'claro_admin_remove_tool_from_role':
            'claro_admin_add_tool_to_role';
        var url = Routing.generate(route, {'role': roleId, 'tool': toolId})

        $.ajax({
            url: url,
            type: 'POST',
            success: function () {
            }
        });
    });
})();