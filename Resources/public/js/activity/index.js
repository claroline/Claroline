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
    $(function () {
        var stackedRequests = 0;
        $('#sortable').sortable({
            update: function () {
                stackedRequests++;
                $('.please-wait').show();
                $.ajax({
                    url: Routing.generate('claro_activity_set_sequence', {
                        'activityId': document.getElementById('twig-attributes').getAttribute('data-activity-id')
                    }),
                    data: {
                        ids: $('#sortable').sortable('toArray')
                    },
                    success: function () {
                        stackedRequests--;
                        if (stackedRequests === 0) {
                            $('.please-wait').hide();
                        }
                    }
                });
            }
        });
        $('#sortable').disableSelection();
    });
})();
