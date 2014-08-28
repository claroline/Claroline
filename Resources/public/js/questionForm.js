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

    var formType = $('#question-form-datas-block').data('form-type');
    var surveyId = $('#question-form-datas-block').data('survey-id');
    
    if (formType === 'edit') {
        var questionId = $('#question-form-datas-block').data('question-id');
    }
    var choiceId;

    function enableTypedQuestionConfiguration()
    {
        var questionType = $('#question_form_type').val();
        
        if (questionType === 'multiple_choice_single' ||
            questionType === 'multiple_choice_multiple') {
        
            var route = (formType === 'create') ?
                Routing.generate(
                    'claro_survey_typed_question_create_form',
                    {
                        'survey': surveyId,
                        'questionType': questionType
                    }
                ) :
                Routing.generate(
                    'claro_survey_typed_question_edit_form',
                    {
                        'survey': surveyId,
                        'question': questionId,
                        'questionType': questionType
                    }
                );
            
            $.ajax({
                url: route,
                type: 'GET',
                async: false,
                success: function (datas) {
                    $('#typed-question-form-block').html('<hr>' + datas);
                    choiceId = parseInt($('#choice-index-block').data('current-choice-index'));
                }
            });
            $('#typed-question-form-block').show('slow', function () {
                $(this).removeClass('hidden');
                enableChoiceOtherLabel();
            });
        } else {
            $('#typed-question-form-block').hide('slow');
        }
    }
    
    function enableCommentLabel()
    {
        var commentChk = $('#question_form_commentAllowed').is(':checked');
        var commentLabelElement = $('#question_form_commentLabel').parents('.form-group');
        
        if (commentChk) {
            commentLabelElement.show('slow');
        } else {
            commentLabelElement.hide('slow');
        }
    }
    
    function enableChoiceOtherLabel()
    {
        var choiceOtherChk = $('#choice-other-chk').is(':checked');
        var choiceOtherElement = $('#choice-other-label-element');
        
        if (choiceOtherChk) {
            choiceOtherElement.show('slow');
        } else {
            choiceOtherElement.hide('slow');
        }
    }

    function addChoice(value)
    {
        choiceId++;
        var newTr = '<tr id="choice-row-' +
            choiceId +
            '"><td><textarea class="claroline-tiny-mce" name="choice[' +
            choiceId +
            ']">';
        
        if (value !== undefined) {
            newTr += value;
        }    
        newTr += '</textarea></td><td style="vertical-align: middle"><span class="btn btn-danger delete-choice-btn" data-choice-id="' +
            choiceId +
            '">' +
            Translator.get('platform' + ':' + 'delete') +
            '</span></td></tr>';
                             
        $('#choices-table').append(newTr);
    }

    function applyModel()
    {
        var modelId = $('#form-model').val();
        
        if (modelId !== 'none') {
            $.ajax({
                url: Routing.generate(
                    'claro_survey_retrieve_model_details',
                    {'survey': surveyId, 'model': modelId}
                ),
                type: 'GET',
                success: function (datas) {
                    var details = $.parseJSON(datas);
                    
                    if (details['questionType']) {
                        $('#question_form_type').val(details['questionType']);
                        enableTypedQuestionConfiguration();
                    }
                    
                    if (details['withComment'] === 'comment') {
                        $('#question_form_commentAllowed').prop('checked', true);
                        $('#question_form_commentLabel').val(details['commentLabel']);
                    } else {
                        $('#question_form_commentAllowed').prop('checked', false);
                    }
                    enableCommentLabel();
                    
                    switch (details['questionType']) {
                        
                        case 'multiple_choice_single':
                        case 'multiple_choice_multiple':
                        
                            if (details['choiceDisplay'] === 'horizontal') {
                                $('#choice-display-form-type').val('horizontal');
                            } else {
                                $('#choice-display-form-type').val('vertical');
                            }
                            
                            var choices = details['choices'];
                            var tableBody = $('#choices-table').children('tbody');
                            tableBody.empty();
                            choiceId = 0;
                            $('#choice-other-chk').prop('checked', false);
                            enableChoiceOtherLabel();
                            
                            for (var i = 0; i < choices.length; i++) {
                                
                                if (choices[i]['other'] === 'other') {
                                    $('#choice-other-chk').prop('checked', true);
                                    enableChoiceOtherLabel();
                                    $('#choice-other-label').val(choices[i]['content']);
                                } else {
                                    addChoice(choices[i]['content']);
                                }
                            }
                            break;
                        default:
                            break;
                    }
                }
            });
        }
    }
    
    $('#form-model').on('change', function () {
        applyModel();
    });
    
    $('#question_form_commentAllowed').on('change', function () {
        enableCommentLabel();
    });
    
    $('#typed-question-form-block').on('change', '#choice-other-chk', function () {
        enableChoiceOtherLabel();
    });

    $('#question_form_type').on('change', function () {
        enableTypedQuestionConfiguration();
    });
    
    $('#typed-question-form-block').on('click', '#add-choice-btn', function () {
        addChoice();
    });

    $('#typed-question-form-block').on('click', '.delete-choice-btn', function () {
        var dataChoiceId = $(this).data('choice-id');
        $('#choice-row-' + dataChoiceId).remove();
    });
    
    $(document).ready(function () {
        enableCommentLabel();
        enableTypedQuestionConfiguration();
        enableChoiceOtherLabel();
    });
})();