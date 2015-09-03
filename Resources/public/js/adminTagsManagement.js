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

    $('#search-tag-btn').on('click', function () {
        var search = $('#search-tag-input').val();
        var orderedBy = $(this).data('ordered-by');
        var order = $(this).data('order');
        var max = $(this).data('max');
        var route = Routing.generate(
            'claro_tag_admin_tags_management',
            {
                'orderedBy': orderedBy,
                'order': order,
                'max': max,
                'search': search
            }
        );

        window.location.href = route;
    });

    $('#search-tag-input').keypress(function(e) {
        if (e.keyCode === 13) {
            var search = $(this).val();
            var orderedBy = $(this).data('ordered-by');
            var order = $(this).data('order');
            var max = $(this).data('max');
            var route = Routing.generate(
                'claro_tag_admin_tags_management',
                {
                    'orderedBy': orderedBy,
                    'order': order,
                    'max': max,
                    'search': search
                }
            );

            window.location.href = route;
        }
    });
})();