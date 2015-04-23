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

    $('.show-tool-edit-form').on('click', function (e) {
        e.preventDefault();
        window.Claroline.Modal.displayForm(
            $(this).attr('href'),
            editName,
            function() {},
            'edit-tool-name'
        );
    });

    function editName(tool) {
        $('#tool-' + tool.tool_id + '-name').html(tool.name);
    }
})();
