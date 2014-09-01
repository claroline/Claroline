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
    var orderedBy = 'title';
    var order = 'ASC';
    var page = 1;
    var max = 20;
    
    function initParams(tab)
    {
        var orderedByFound = false;
        var orderFound = false;
        var pageFound = false;

        for (var i = 0; i < tab.length; i++) {
            
            switch (tab[i]) {
                
                case 'ordered':
                    
                    if (tab[i + 1] === 'by' &&
                        (tab[i + 2] === 'title' || tab[i + 2] === 'type')) {
                    
                        orderedBy = tab[i + 2];
                        orderedByFound = true;
                        i = i + 2;
                    }
                    break;
                case 'order':
                    
                    if (orderedByFound &&
                        (tab[i + 1] === 'ASC' || tab[i + 1] === 'DESC')) {
                    
                        order = tab[i + 1];
                        orderFound = true;
                        i++;
                    }
                    break;
                case 'page':
                    
                    if (orderFound && tab[i + 1] !== undefined) {
                    
                        page = tab[i + 1];
                        pageFound = true;
                        i++;
                    }
                    break;
                case 'max':
                    
                    if (pageFound && tab[i + 1] !== undefined) {
                    
                        max = tab[i + 1];
                        i++;
                    }
                    break;
                default:
                    break;
                    
            }
        }
    }
    
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

    $('#add-question-box').on('click', 'a:not(.add-question-btn)', function (event) {
        event.preventDefault();
        event.stopPropagation();
        
        var url = $(this).attr('href');
        var urlTab = url.split('/');
        initParams(urlTab);
        
        $.ajax({
            url: Routing.generate(
                'claro_survey_questions_list',
                {
                    'survey': surveyId,
                    'orderedBy': orderedBy,
                    'order': order,
                    'page': page,
                    'max': max
                }
            ),
            type: 'GET',
            async: false,
            success: function (result) {
                $('#add-question-body').html(result);
            }
        });
    });

    $('#question-table').sortable({
        items: 'tr',
        cursor: 'move'
    });

    $('#question-table').on('sortupdate', function (event, ui) {
        
        if (this === ui.item.parents('#question-table')[0]) {
            var relationId = $(ui.item).data('question-relation-id');
            var otherRelationId = $(ui.item).next().data('question-relation-id');
            var mode = 'previous';
            var execute = false;
            
            if (otherRelationId !== undefined) {
                mode = 'next';
                execute = true;
            } else {
                otherRelationId = $(ui.item).prev().data('question-relation-id');
                
                if (otherRelationId !== undefined) {
                    execute = true;
                }
            }
            
            if (execute) {
                $.ajax({
                    url: Routing.generate(
                        'claro_survey_update_question_order',
                        {
                            'survey': surveyId,
                            'relation': relationId,
                            'otherRelation': otherRelationId,
                            'mode': mode
                        }
                    ),
                    type: 'POST'
                });
            }
        }
    });
})();