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
    
    $('#workspace-display-edit-form').on('focus', '#workspace_options_form_backgroundColor', function () {
        $(this).colorpicker();
    });
    
    $('#workspace_options_form_backgroundColor').colorpicker().on('changeColor', function(ev){
        $('body').css('background-color', ev.color.toHex());
    });
})();