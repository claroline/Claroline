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
    
    $('.edit-mandatory-btn').on('click', function () {
        var mandatoryBtn = $(this);
        var relationId = parseInt(mandatoryBtn.data('relation-id'));
        
        $.ajax({
            url: Routing.generate(
                'claro_survey_question_relation_mandatory_switch',
                {
                    'relation': relationId
                }
            ),
            type: 'GET',
            success: function (datas) {
                
                if (datas === 'mandatory') {
                    mandatoryBtn.removeClass('btn-default');
                    mandatoryBtn.addClass('btn-success');
                    mandatoryBtn.attr(
                        'data-original-title',
                        Translator.get('survey:mandatory_answer_message')
                    );
                } else {
                    mandatoryBtn.removeClass('btn-success');
                    mandatoryBtn.addClass('btn-default');
                    mandatoryBtn.attr(
                        'data-original-title',
                        Translator.get('survey:not_mandatory_answer_message')
                    );
                }
            }
        });
    });
    
    $('.view-survey-btn').on('click', function () {
        var surveyId = $(this).data('survey-id');
        
        $.ajax({
            url: Routing.generate(
                'claro_survey_display',
                {'survey': surveyId}
            ),
            type: 'GET',
            success: function (datas) {
                $('#view-survey-body').html(datas);
                $('#view-survey-box').modal('show');
            }
        });
    });
})();