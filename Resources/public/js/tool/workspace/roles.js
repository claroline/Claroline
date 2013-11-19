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

    $('.role-delete-btn').on('click', function (event) {
        console.debug(event);
        event.preventDefault();

        if (!$(event.target).hasClass('disabled')) {
            $.ajax({
                url: $(event.target).attr('href'),
                type: 'GET',
                success: function () {
                    $(event.target).parent().parent().remove();
                }
            });
        }
    });
})();