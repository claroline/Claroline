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
    
    var surveyId = parseInt($('#survey-data-element').data('survey-id'));
    
    $('#add-question-btn').on('click', function () {
        
        $.ajax({
            url: Routing.generate(
                'claro_survey_questions_list',
                {'survey': surveyId}
            ),
            type: 'GET',
            success: function (datas) {
                $('#add-question-body').html(datas);
                $('#add-question-box').modal('show');
            }
        });
    });
    
    $('#add-question-confirm-ok').on('click', function () {
        $('#add-question-box').modal('hide');
    });  
    
    $('.view-question-btn').on('click', function () {
        var questionId = parseInt($(this).data('question-id'));
        
        $.ajax({
            url: Routing.generate(
                'claro_survey_typed_question_display',
                {
                    'survey': surveyId,
                    'question': questionId
                }
            ),
            type: 'GET',
            success: function (datas) {
                $('#view-question-body').html(datas);
                $('#view-question-box').modal('show');
            }
        });
    });
})();