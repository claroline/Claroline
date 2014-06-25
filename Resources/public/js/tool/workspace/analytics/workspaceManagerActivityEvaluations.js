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
    
    $('.display-past-evaluations-link').on('click', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        
        var route = $(this).attr('href');
        
        $.ajax({
            url: route,
            type: 'GET',
            success: function (datas) {
                $('#activity-past-evaluations-modal-body').empty();
                $('#activity-past-evaluations-modal-body').html(datas);
            }
        });
        $('#activity-past-evaluations-modal-box').modal('show');
    });
    
    $('.evaluation-edit-button').on('click', function () {
        var userId = $(this).data('user-id');
        var activityParamsId = $(this).data('activity-parameters-id');
        var evaluationId = $(this).data('evaluation-id');
        
        if (typeof evaluationId === 'undefined' ) {
            console.log('undefined p-e');
        } else {
            console.log(evaluationId);
        }
    });
})();