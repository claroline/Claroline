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
    
    $('#activity-past-evaluations-modal-box').on('click', '.past-evaluation-edit-button', function () {
        var pastEvaluationId = $(this).data('past-evaluation-id');
        
        if (typeof pastEvaluationId !== 'undefined' ) {
            $.ajax({
                url: Routing.generate(
                    'claro_activity_past_evaluation_edit',
                    {'pastEvaluationId': pastEvaluationId}
                ),
                type: 'GET',
                success: function (datas) {
                    $('#activity-past-evaluation-edition-modal-body').empty();
                    $('#activity-past-evaluation-edition-modal-body').html(datas);
                    $('#activity-past-evaluation-edition-modal-box').modal('show');
                    $('#activity-past-evaluations-modal-box').modal('hide');
                }
            });
        }
    });
    
    $('#activity-past-evaluation-edition-cancel-btn').on('click', function () {
        $('#activity-past-evaluation-edition-modal-body').empty();
        $('#activity-past-evaluations-modal-box').modal('show');
        $('#activity-past-evaluation-edition-modal-box').modal('hide');
    });
    
    $('#activity-past-evaluation-edition-validate-btn').on('click', function () {
        var form = document.getElementById('activity-past-evaluation-edit-form');
        var action = form.getAttribute('action');
        var formData = new FormData(form);
        var workspaceId = $('#activity-evaluations').data('workspace-id');
        var activityParametersId = $('#activity-evaluations').data('activity-parameters-id');
        var userId = $('#activity-past-evaluation-edit-form').data('user-id');
        
        $.ajax({
            url: action,
            data: formData,
            type: 'POST',
            processData: false,
            contentType: false,
            complete: function(jqXHR) {
                switch (jqXHR.status) {
                    case 204:
                        $.ajax({
                            url: Routing.generate(
                                'claro_workspace_activities_past_evaluations_show',
                                {
                                    'workspaceId': workspaceId,
                                    'userId': userId,
                                    'activityParametersId': activityParametersId,
                                    'displayType': 'user'
                                }
                            ),
                            type: 'GET',
                            success: function (datas) {
                                $('#activity-past-evaluations-modal-body').empty();
                                $('#activity-past-evaluations-modal-body').html(datas);
                                $('#activity-past-evaluations-modal-box').modal('show');
                                $('#activity-past-evaluation-edition-modal-body').empty();
                                $('#activity-past-evaluation-edition-modal-box').modal('hide');
                            }
                        });
                        break;
                    default:
                        $('#activity-past-evaluation-edition-modal-body').html(jqXHR.responseText);
                }
            }
        });
    });
    
    $('.display-comment').popover();
})();