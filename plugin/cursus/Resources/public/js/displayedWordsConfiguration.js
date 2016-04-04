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
    
    $('.displayed-word-change-btn').on('click', function () {
        var word = $(this).data('word');
        var inputId = $(this).data('input-id');
        var value = $('#' + inputId).val();
        
        $.ajax({
            url: Routing.generate(
                'claro_cursus_change_displayed_word',
                {
                    'key': word,
                    'value': value
                }
            ),
            type: 'POST',
            success: function () {
                location.reload();
            }
        });
    });
})();