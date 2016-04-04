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

    function showButton() {
        $('.read-more .show-more').addClass('hide');
        $('.read-more .text-gradient').removeClass('hide');

        $('.read-more').parent().each(function () {
            if ($(this).outerHeight(true) >= 330) {
                $('.show-more', this).removeClass('hide');
            } else {
                $('.text-gradient', this).addClass('hide');
            }
        });
    }

    $('body').on('click', '.show-more', function () {
        var content = $(this).parents('.content-element').first();

        $(this).remove();

        $('.read-more', content).removeClass('read-more');
        $(' > .panel', content).css('height', 'auto');
        $('.contents').trigger('ContentModified');
    });

    /** triggers **/

    var resizeWindow;
    var domChange;

    $(window).on('resize', function () {
        clearTimeout(resizeWindow);
        resizeWindow = setTimeout(showButton, 500);
    })
    .load(function () {
        clearTimeout(resizeWindow);
        resizeWindow = setTimeout(showButton, 500);
    });

    $(document).ready(function () {
        clearTimeout(resizeWindow);
        resizeWindow = setTimeout(showButton, 500);
    });

    $('.contents').bind('ContentModified', function () {
        clearTimeout(domChange);
        domChange = setTimeout(showButton, 500);
    });

}());
