/**
 * Created by Aurelien on 24/07/14.
 * specific script for editCriteriaPage. need instancied variables :
 *          var totalColumn
 *          var comment
 *          var nbCorrection
 *          var nbResults
 */
var manualSubmit = false;

$(document).ready(function () {
    setSaveListener();
    $('#innova_collecticiel_criteria_form_goBack').val(0);
    $('.back-button').on('click', function (event) {
        event.preventDefault();
        $('#innova_collecticiel_criteria_form_goBack').val(1);
        $('.save-submit').trigger('click');
    });

    $('.column-input').hide();
    $('.column-input-js').show();


    $('#innova_collecticiel_criteria_form_allowCommentInCorrection').on('click', function () {
        if (comment == 0) {
            comment = 1;
        } else {
            comment = 0;
        }
        $('.comment-input input').val(comment);
    });

    var setColumnInput = function () {
        $('.column-container').empty();
        for (i = 0; i < totalColumn; i++) {
            $('.column-container').append('<input type="radio" disabled style="margin-right: 4px; margin-left: 0px; padding-right: 0px; padding-left: 0px"/>');
        }
    };

    $('.add-column').on('click', function (event) {
        event.preventDefault();
        if (totalColumn < 10) {
            totalColumn++;
            setColumnInput();

            $('.column-input input').val(totalColumn);
        }
    });

    $('.remove-column').on('click', function (event) {
        event.preventDefault();
        if (totalColumn > 3) {
            totalColumn--;
            setColumnInput();

            $('.column-input input').val(totalColumn);
        }
    });

    function resetTiny() {
        $('.tinymce').each(function () {
            $(this).tinymce().remove();
        });
    }

//var form_count = 0;
    $('.add-criterion-button-innova').on('click', function (event) {

        event.preventDefault();


        $('.disabled-during-edition').attr('disabled', 'disabled');
        tinymce.get('innova_collecticiel_criteria_form_correctionInstruction').getBody().setAttribute('contenteditable', false);
        //$('.innova_collecticiel_criteria_form_correctionInstruction').attr('disabled','disabled');
        $('.criteria-form-button').attr('disabled', 'disabled');

        var criterionId = $(this).data('criterion');
        var $form = $('#global_form');
        $('#addCriteriaReRouting').val('add-criterion');
        $.ajax({
            url: $form.attr('action'),
            type: $form.attr('method'),
            data: $form.serialize(),
            success: function (data) {
                $.get($('.add-criterion-button').attr('href'))
                    .done(function (data) {
                        resetTiny();
                        $('.new-criteria-zone').empty();
                        $('.criterion-row > .criterion-edit').empty();

                        $('.template > .template-criteria-zone').clone().appendTo('.new-criteria-zone');
                        $('.new-criteria-zone .new-criteria-form').append(data);

                        $('.column-input input').val(totalColumn);
                        $('.comment-input input').val(comment);

                        $('.add-criteria-zone').hide();
                        $('.new-criteria').show();
                        $('.new-criteria-zone > .template-criteria-zone').show();

                        $('.new-criteria-zone > .template-criteria-zone .form-buttons').hide();

                        $('.add-remove-column').show();

                        var top = $('#new-criteria').offset().top;
                        top = top - 50;
                        $('body,html').scrollTop(top);
                        setSaveListener();
                    })
                ;

            }
        });
    });

    $('.delete-criteria-button').click(function (event) {
        event.preventDefault();
        var $form = $('#global_form');
        $('#addCriteriaReRouting').val('add-criterion');
        var $link = $(this);
        $.ajax({
            url: $form.attr('action'),
            type: $form.attr('method'),
            data: $form.serialize(),
            success: function () {
                $.get($link.attr('href'));
            }
        })
        ;
    });

    var temp_edit_criteria_url = null;
    $('.edit-criterion-button').on('click', function (event) {
        event.preventDefault();
        temp_edit_criteria_url = $(this).attr('href');
        $('.disabled-during-edition').attr('disabled', 'disabled');
        $('.criteria-form-button').attr('disabled', 'disabled');

        var criterionId = $(this).data('criterion');
        var $form = $('#global_form');
        $('#addCriteriaReRouting').val('add-criterion');

        $.ajax({
            url: $form.attr('action'),
            type: $form.attr('method'),
            data: $form.serialize(),
            success: function () {
                $.get(temp_edit_criteria_url)
                    .done(function (data) {
                        temp_edit_criteria_url = null;
                        resetTiny();
                        $('.new-criteria-zone').empty();
                        $('.criterion-row > .criterion-edit').empty();

                        $('.template > .template-criteria-zone').clone().appendTo('#' + criterionId + '  .criterion-edit');
                        $('#' + criterionId + ' .criterion-edit .new-criteria-form').append(data);

                        $('#' + criterionId + ' .criterion-edit .form-cancel').data('criterion', criterionId);
                        $('.column-input input').val(totalColumn);
                        $('.comment-input input').val(comment);

                        $('#' + criterionId + '  .criterion-show').hide();
                        $('#' + criterionId + '  .criterion-edit').show();
                        $('#' + criterionId + '  .criterion-edit  .template-criteria-zone').show();

                        $('#' + criterionId + ' .criterion-edit  .template-criteria-zone .form-buttons').hide();

                        setSaveListener();
                    })
                ;

            }
        });


    });

    $('form').submit(function (e) {
        if (nbCorrection > 0 && !manualSubmit) {
            e.preventDefault();
            $('#recalculateAskPopup').modal('show');
            manualSubmit = true;
            $('#recalculateButton').unbind('click').click(function () {
                $('#innova_collecticiel_criteria_form_recalculateGrades').val(1);
                $('form').submit();
            });

            $('#notRecalculateButton').unbind('click').click(function () {
                $('form').submit();
            });
        }
    });


});

function setSaveListener() {
    $('.form-submit').unbind('click').click(function (event) {
        event.preventDefault();
        //I do the "click" on submit button for keep html5 warning
        $('.inline-body button[type="submit"]').trigger('click');
    });

    $('.form-cancel').unbind('click').click(function (event) {
        event.preventDefault();
        var criterionId = $(this).data('criterion');
        if (criterionId == 'new') {
            $('.new-criteria').hide();
            $('.add-criteria-zone').show();

            if ((nbResults == 0) ? 'true' : 'false') {
                $('.add-remove-column').hide();
            }
        } else {
            $('#' + criterionId + ' > .criterion-edit').hide();
            $('#' + criterionId + ' > .criterion-show').show();
        }

        $('.disabled-during-edition').attr('disabled', null);
        $('.criteria-form-button').attr('disabled', null);
        tinymce.get('innova_collecticiel_criteria_form_correctionInstruction').getBody().setAttribute('contenteditable', true);
    });
}
