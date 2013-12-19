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

    var home = window.Claroline.Home;

    $('body').on('click', '.locale-select', function () {
        if (!$(this).parents('.modal').get(0)) {
            home.modal('locale/select');
        } else {
            $.ajax(home.path + 'locale/change/' + $(this).html().toLowerCase())
            .done(function () {
                window.location.reload();
            });
        }
    });

}());
