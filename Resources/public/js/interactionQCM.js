var container = $('div#ujm_exobundle_interactionqcmtype_choices');
var tableChoices = $('#tableChoice');
var index;

function creationQCM(expectedAnswer, response, point, comment, positionForce) {

    tableChoices.append('<table id="newTable" class="table table-striped table-bordered table-condensed"><thead><tr style="background-color: lightsteelblue;"><th class="classic">'+expectedAnswer+'</th><th class="classic">'+response+'</th><th class="classic">'+point+'</th><th class="classic">'+comment+'</th><th class="classic">'+positionForce+'</th><th class="classic">-----</th></tr></thead><tbody><tr></tr></tbody></table>');

    var add = $('<a href="#" id="add_choice" class="btn btn-primary"><i class="icon-plus"></i>&nbsp;Ajouter un choix</a>');

    tableChoices.append(add);

    add.click(function (e) {
        $('#newTable').find('tbody').append('<tr></tr>');
        addChoice(container);
        e.preventDefault();
        return false;
    });

    index = container.find(':input').length;

    if (index == 0) {
        addChoice(container);
        $('#newTable').find('tbody').append('<tr></tr>');
        addChoice(container);
    } else {
        tableChoices.children('tr').each(function() {
        addDelete($(this));
        });
    }

    $('#ujm_exobundle_interactionqcmtype_weightResponse').change(function () {
        if ($(this).is(':checked')) {
            $('#ujm_exobundle_interactionqcmtype_scoreRightResponse').prop('disabled', true);
            $('#ujm_exobundle_interactionqcmtype_scoreFalseResponse').prop('disabled', true);

            $("*[id$='_weight']").each(function() {
                $(this).prop('disabled', false);
            });
        } else {
            $('#ujm_exobundle_interactionqcmtype_scoreRightResponse').prop('disabled', false);
            $('#ujm_exobundle_interactionqcmtype_scoreFalseResponse').prop('disabled', false);

            $("*[id$='_weight']").each(function() {
                $(this).prop('disabled', true);
            });
        }
    });

    $('#ujm_exobundle_interactionqcmtype_shuffle').change(function () {
        if ($(this).is(':checked')) {
            tableChoices.find('th').eq(4).show();
            $("*[id$='_positionForce']").each(function () {
                $(this).parent('td').show();
            });
        } else {
            tableChoices.find('th').eq(4).hide();
            $("*[id$='_positionForce']").each(function () {
               $(this).parent('td').hide();
           });
        }
    });

    $('#ujm_exobundle_interactionqcmtype_typeQCM').change(function () {
        var type = $('#ujm_exobundle_interactionqcmtype_typeQCM option:selected').val();

        $("*[id$='_rightResponse']").each(function () {
            if (type == 1) {
                $(this).prop('type', 'checkbox');
            } else {
                $(this).prop('type', 'radio');
                $(this).attr('checked', false);
            }
        });
    });

    $(':radio').live('click', function () {
       if ($(this).is(':checked')) {
           $('#newTable').find(('tr:not(:first)')).each(function () {
              $(this).find('input:first').removeAttr('checked');
           });

           $(this).attr('checked', 'checked');
       }
    });

    $('tbody').sortable();
}

// -_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_ //

function creationQCMEdit(expectedAnswer, response, point, comment, positionForce) {

    tableChoices.append('<table id="newTable" class="table table-striped table-bordered table-condensed"><thead><tr style="background-color: lightsteelblue;"><th class="classic">'+expectedAnswer+'</th><th class="classic">'+response+'</th><th class="classic">'+point+'</th><th class="classic">'+comment+'</th><th class="classic">'+positionForce+'</th><th class="classic">-----</th></tr></thead><tbody></tbody></table>');

    var add = $('<a href="#" id="add_choice" class="btn btn-primary"><i class="icon-plus"></i>&nbsp;Ajouter un choix</a>');

    tableChoices.append(add);

    container.children().first().children('div').each(function () {

        $('#newTable').find('tbody').append('<tr></tr>');

         $(this).find('.row').each(function () {

            if ($(this).find('input').length) {
                if ($(this).find('input').attr('id').indexOf('ordre') == -1) {
                    $('#newTable').find('tr:last').append('<td class="classic"></td>');
                    $('#newTable').find('td:last').append($(this).find('input'));
                } else {
                    $('#newTable').find('tr:last').append('<td class="classic" style="display:none;"></td>');
                    $('#newTable').find('td:last').append($(this).find('input'));
                }
            }

            if ($(this).find('textarea').length) {
                $('#newTable').find('tr:last').append('<td class="classic"></td>');
                $('#newTable').find('td:last').append($(this).find('textarea'));
            }
            
            $('#choiceError').append($(this).find('span'));
        });

        $('#newTable').find('tr:last').append('<td class="classic"></td>');
        addDelete($('#newTable').find('td:last'));
    });

    container.remove();
    tableChoices.next().remove();

    index = $('#newTable').find('tr:not(:first)').length;

    add.click(function (e) {
        $('#newTable').find('tbody').append('<tr></tr>');
        addChoice(container);
        e.preventDefault();
        return false;
    });

    if ($('#ujm_exobundle_interactionqcmtype_shuffle').is(':checked')) {
        tableChoices.find('th').eq(4).show();
        $("*[id$='_positionForce']").each(function () {
            $(this).parent('td').show();
        });
    } else {
        tableChoices.find('th').eq(4).hide();
        $("*[id$='_positionForce']").each(function () {
           $(this).parent('td').hide();
       });
    }

    if ($('#ujm_exobundle_interactionqcmtype_weightResponse').is(':checked')) {
        $('#ujm_exobundle_interactionqcmtype_scoreRightResponse').prop('disabled', true);
        $('#ujm_exobundle_interactionqcmtype_scoreFalseResponse').prop('disabled', true);

        $("*[id$='_weight']").each(function() {
            $(this).prop('disabled', false);
        });
    } else {
        $('#ujm_exobundle_interactionqcmtype_scoreRightResponse').prop('disabled', false);
        $('#ujm_exobundle_interactionqcmtype_scoreFalseResponse').prop('disabled', false);

        $("*[id$='_weight']").each(function() {
            $(this).prop('disabled', true);
        });
    }

    var type = $('#ujm_exobundle_interactionqcmtype_typeQCM option:selected').val();

    $("*[id$='_rightResponse']").each(function () {
        if (type == 1) {
            $(this).prop('type', 'checkbox');
        } else {
            $(this).prop('type', 'radio');
        }
    });

    $('#ujm_exobundle_interactionqcmtype_weightResponse').change(function () {
        if ($(this).is(':checked')) {
            $('#ujm_exobundle_interactionqcmtype_scoreRightResponse').prop('disabled', true);
            $('#ujm_exobundle_interactionqcmtype_scoreFalseResponse').prop('disabled', true);

            $("*[id$='_weight']").each(function() {
                $(this).prop('disabled', false);
            });
        } else {
            $('#ujm_exobundle_interactionqcmtype_scoreRightResponse').prop('disabled', false);
            $('#ujm_exobundle_interactionqcmtype_scoreFalseResponse').prop('disabled', false);

            $("*[id$='_weight']").each(function() {
                $(this).prop('disabled', true);
            });
        }
    });

    $('#ujm_exobundle_interactionqcmtype_shuffle').change(function () {
        if ($(this).is(':checked')) {
            tableChoices.find('th').eq(4).show();
            $("*[id$='_positionForce']").each(function () {
                $(this).parent('td').show();
            });
        } else {
            tableChoices.find('th').eq(4).hide();
            $("*[id$='_positionForce']").each(function () {
               $(this).parent('td').hide();
           });
        }
    });

    $('#ujm_exobundle_interactionqcmtype_typeQCM').change(function () {
        var type = $('#ujm_exobundle_interactionqcmtype_typeQCM option:selected').val();

        $("*[id$='_rightResponse']").each(function () {
            if (type == 1) {
                $(this).prop('type', 'checkbox');
            } else {
                $(this).prop('type', 'radio');
                $(this).attr('checked', false);
            }
        });
    });

    $('tbody').sortable();
}

// -_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_ //

function setOrder() {

    var order = 1;

    $('#newTable').find('tr:not(:first)').each(function () {
        $(this).find('input:first').val(order);
        order++;
    });
}

function check_form(nbrChoices, answerCoched, labelEmpty, pointAnswers, pointAnswer, inviteQuestion) {
    //"use strict";

    //vérifier que l'invite de la question est rempli
    if ($('#ujm_exobundle_interactionqcmtype_interaction_invite').val() == '') {
        alert(inviteQuestion);
        return false;
    } else {
        //vérifier qu'il y a au moins deux choix
        if (($('#newTable').find('tr:not(:first)').length) < 2) {
            alert(nbrChoices);
            return false;
        } else {
            //vérifier qu'il y a des réponse attendue
            var nbr_rep_coched = 0;
            $('#newTable').find('tr:not(:first)').each(function (index) {
                if ($(this).find('td').eq(1).find('input').is(':checked')) {
                    nbr_rep_coched = nbr_rep_coched + 1;
                }
            });
            if (nbr_rep_coched === 0) {
                alert(answerCoched);
                return false;
            } else {
            //vérifier les points des reponses
                if ($('#ujm_exobundle_interactionqcmtype_weightResponse').is(':checked')) {
                    var checked = true;
                    $('#newTable').find('tr:not(:first)').each(function (index) {

                        if ($(this).find('td').eq(3).find('input').val() == '') {
                            checked = false;
                            return false;
                        }
                    });

                    if (checked == false) {
                        alert(pointAnswers);
                        return false;
                    }
                }
            }
        }
    }
}

function addChoice(container) {

    var contain = $(container.attr('data-prototype').replace(/__name__label__/g, 'Choice n°' + (index))
        .replace(/__name__/g, index)
        .replace('<a class="btn btn-danger remove" href="#">Delete</a>', '')
    );

    addDelete(contain);

    container.append(contain);

    container.find('.row').each(function () {

        if ($(this).find('input').length) {
            if ($(this).find('input').attr('id').indexOf('ordre') == -1) {
                $('#newTable').find('tr:last').append('<td class="classic"></td>');
                $('#newTable').find('td:last').append($(this).find('input'));
            } else {
                $('#newTable').find('tr:last').append('<td class="classic" style="display:none;"></td>');
                $('#newTable').find('td:last').append($(this).find('input'));
            }
        }

        if ($(this).find('textarea').length) {
            $('#newTable').find('tr:last').append('<td class="classic"></td>');
            $('#newTable').find('td:last').append($(this).find('textarea'));
        }
    });

    $('#newTable').find('tr:last').append('<td class="classic"></td>');
    $('#newTable').find('td:last').append(container.find('a:contains("Supprimer")'));

    container.remove();
    tableChoices.next().remove();

    index++;

    if ($('#ujm_exobundle_interactionqcmtype_weightResponse').is(':checked')) {

        $("*[id$='_weight']").each(function () {
            $(this).prop('disabled', false);
        });
    } else {

        $("*[id$='_weight']").each(function () {
            $(this).prop('disabled', true);
        });
    }

    if ($('#ujm_exobundle_interactionqcmtype_shuffle').is(':checked')) {
        tableChoices.find('th').eq(4).show();
        $("*[id$='_positionForce']").each(function () {
            $(this).parent('td').show();
        });
    } else {
        tableChoices.find('th').eq(4).hide();
        $("*[id$='_positionForce']").each(function () {
           $(this).parent('td').hide();
       });
    }

    if ($('#ujm_exobundle_interactionqcmtype_typeQCM option:selected').val() == 1) {
        $("*[id$='_rightResponse']").each(function () {
            $(this).prop('type', 'checkbox');
        });
    } else {
        $("*[id$='_rightResponse']").each(function () {
            $(this).prop('type', 'radio');
        });
    }
}

function addDelete(tr) {

    var delLink = $('<a href="#" class="btn btn-danger">Supprimer</a>');

    tr.append(delLink);

    delLink.click(function(e) {
        $(this).parent('td').parent('tr').remove();
        e.preventDefault();
        return false;
    });
}