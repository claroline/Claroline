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
        window.Claroline.Modal.fromUrl(
            Routing.generate('claro_user_picker'),
            null
        );
    });
})();