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
    $(':submit').on('click', function (e) {
        e.preventDefault();
        var formAction = $(e.currentTarget.parentElement).attr('action');
        var form = document.getElementById('form-resource-creation-rights');
        var formData = new FormData(form);
        $.ajax({
            url: formAction,
            data: formData,
            type: 'POST',
            processData: false,
            contentType: false
        });
    });
})();