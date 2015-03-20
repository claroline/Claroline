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
    
    $('body').on('focus', '#user_options_form_desktopBackgroundColor', function () {
        $(this).colorpicker();
    });
    
    $('#user_options_form_desktopBackgroundColor').colorpicker().on('changeColor', function(ev){
        $('body').css('background-color', ev.color.toHex());
    });
})();