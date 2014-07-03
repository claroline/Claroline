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
        var evaluationId = $(this).data('evaluation-id');
        
        if (typeof evaluationId !== 'undefined' ) {
            $.ajax({
                url: Routing.generate(
                    'claro_activity_evaluation_edit',
                    {'evaluationId': evaluationId}
                ),
                type: 'GET',
                success: function (datas) {
                    $('#activity-evaluation-edition-modal-body').empty();
                    $('#activity-evaluation-edition-modal-body').html(datas);
                }
            });
            $('#activity-evaluation-edition-modal-box').modal('show');
        }
    });
    
    $('#activity-evaluation-edition-validate-btn').on('click', function () {
        var form = document.getElementById('activity-evaluation-edit-form');
        var action = form.getAttribute('action');
        var formData = new FormData(form);
        
        $.ajax({
            url: action,
            data: formData,
            type: 'POST',
            processData: false,
            contentType: false,
            complete: function(jqXHR) {
                switch (jqXHR.status) {
                    case 204:
                        window.location.reload();
                        break;
                    default:
                        $('#activity-evaluation-edition-modal-body').html(jqXHR.responseText);
                }
            }
        });
    });
    
    $('.display-comment').popover();
})();