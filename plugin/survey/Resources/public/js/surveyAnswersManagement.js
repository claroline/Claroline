
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

    var checkedAnswersIds = [];
    var surveyId = $('#datas-box').data('survey-id');

    function addCheckedAnswer(id) {
        var index = checkedAnswersIds.indexOf(id);

        if (index === -1) {
            checkedAnswersIds.push(id);
        }
    };

    function removeCheckedAnswer(id) {
        var index = checkedAnswersIds.indexOf(id);

        if (index > -1) {
            checkedAnswersIds.splice(index, 1);
        }
    };

    $('#select-all-chk').on('click', function () {
        if ($(this).prop('checked')) {
            $('.answer-chk').each(function () {
                $(this).prop('checked', true);
                addCheckedAnswer($(this).val());
            });
        } else {
            $('.answer-chk').each(function () {
                $(this).prop('checked', false);
                removeCheckedAnswer($(this).val());
            });
        }
        if (checkedAnswersIds.length > 0) {
            $('#delete-selected-answers-btn').removeClass('disabled');
        } else {
            $('#delete-selected-answers-btn').addClass('disabled');
        }
    });

    $('.answer-chk').on('click', function () {
        var value = $(this).val();

        if ($(this).prop('checked')) {
            addCheckedAnswer(value);
        } else {
            removeCheckedAnswer(value);
        }
        if (checkedAnswersIds.length > 0) {
            $('#delete-selected-answers-btn').removeClass('disabled');
        } else {
            $('#delete-selected-answers-btn').addClass('disabled');
        }
    });

    $('#delete-all-answers-btn').on('click', function () {
        window.Claroline.Modal.confirmRequest(
            Routing.generate('claro_survey_all_answers_delete', {survey: surveyId}),
            emptySurveyAnswers,
            null,
            Translator.trans('all_survey_answers_deletion_confirm_message', {}, 'survey'),
            Translator.trans('all_survey_answers_deletion', {}, 'survey')
        );
    });

    $('#delete-selected-answers-btn').on('click', function () {
        var route = Routing.generate('claro_survey_answers_delete', {survey: surveyId});
        var parameters = {};
        parameters.surveyAnswersIds = checkedAnswersIds;
        route += '?' + $.param(parameters);
        window.Claroline.Modal.confirmRequest(
            route,
            removeSurveyAnswersRows,
            null,
            Translator.trans('selected_answers_deletion_confirm_message', {}, 'survey'),
            Translator.trans('selected_answers_deletion', {}, 'survey')
        );
    });

    var emptySurveyAnswers = function () {
        var content = '<div class="alert alert-warning">' + Translator.trans('no_answer', {}, 'survey') + '</div>';
        $('#survey-answers-management-content').html(content);
    };

    var removeSurveyAnswersRows = function () {
        checkedAnswersIds.forEach(function (answerId) {
            $('#answer-row-' + answerId).remove();
        });
        checkedAnswersIds = [];
        $('#delete-selected-answers-btn').addClass('disabled');
    };
})();
