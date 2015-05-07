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
    
    $('body').on('click', '.user-picker', function () {
        var multiple = $(this).data('multiple');
        var showUsername = $(this).data('show-username');
        var showMail = $(this).data('show-mail');
        var showCode = $(this).data('show-code');
        var userIdsTxt = $(this).data('excluded-users');
        userIdsTxt = userIdsTxt.trim();
        var userIds = userIdsTxt !== '' ?
            userIdsTxt.split(';') :
            [];
        var parameters = {};
        parameters.excludedUserIds = userIds;
        
        if (multiple === undefined) {
            multiple = 'multiple';
        }
        var route = Routing.generate(
            'claro_user_picker',
            {
                'mode': multiple,
                'showUsername': showUsername,
                'showMail': showMail,
                'showCode': showCode
            }
        );
        route += '?' + $.param(parameters);
        
        window.Claroline.Modal.fromUrl(
            route,
            null
        );
    });
})();