var container = $('div#ujm_exobundle_interactionqcmtype_choices'); // Div which contain the dataprototype
var tableChoices = $('#tableChoice'); // div which contain the choices array

// QCM Creation
function creationQCM(expectedAnswer, response, point, comment, positionForce, addchoice, deleteChoice) {

    var index; // number of choices

    tableChoicesCreation(expectedAnswer, response, point, comment, positionForce, addchoice, deleteChoice, 0);

    // Number of choice initially
    index = container.find(':input').length;

    // If no choice exist, add two choices by default
    if (index == 0) {
        addChoice(container, deleteChoice);
        $('#newTable').find('tbody').append('<tr></tr>');
        addChoice(container, deleteChoice);
    // If choice already exist, add button to delete it
    } else {
        tableChoices.children('tr').each(function() {
            addDelete($(this), deleteChoice);
        });
    }

    whichChange();

    // when select a radio box, deselect the other because can only have one selected
    $(':radio').live('click', function () {
       if ($(this).is(':checked')) {
           $('#newTable').find(('tr:not(:first)')).each(function () {
              $(this).find('input').eq(1).removeAttr('checked');
           });

           $(this).attr('checked', 'checked');
       }
    });

    // Make the choices' table sortable with jquery ui plugin
    $('tbody').sortable();
}

// QCM Edition
function creationQCMEdit(expectedAnswer, response, point, comment, positionForce, addchoice, deleteChoice, nbResponses) {

    var index;

    tableChoicesCreation(expectedAnswer, response, point, comment, positionForce, addchoice, deleteChoice, nbResponses);

    // Get the form field to fill rows of the choices' table
    container.children().first().children('div').each(function () {

        // Add a row to the table
        $('#newTable').find('tbody').append('<tr></tr>');

         $(this).find('.row').each(function () {

            fillChoicesArray($(this));

            // Add the form errors
            $('#choiceError').append($(this).find('span'));
        });

        if (nbResponses == 0) {
            // Add the delete button
            $('#newTable').find('tr:last').append('<td class="classic"></td>');
            addDelete($('#newTable').find('td:last'), deleteChoice);
        }
    });

    // Remove the useless fields form
    container.remove();
    tableChoices.next().remove();

    // Get the number of choices
    index = $('#newTable').find('tr:not(:first)').length;

    whichChecked();
    whichChange();

    $(':radio').live('click', function () {
       if ($(this).is(':checked')) {
           $('#newTable').find(('tr:not(:first)')).each(function () {
              $(this).find('input').eq(1).removeAttr('checked');
           });

           $(this).attr('checked', 'checked');
       }
    });

    // Make the choices' table sortable with jquery ui plugin
    $('tbody').sortable();
}

// Add a choice
function addChoice(container, deleteChoice) {

    var index = $('#newTable').find('tr:not(:first)').length;
    // change the "name" by the index and delete the symfony delete form button
    var contain = $(container.attr('data-prototype').replace(/__name__label__/g, 'Choice n°' + (index))
        .replace(/__name__/g, index)
        .replace('<a class="btn btn-danger remove" href="#">Delete</a>', '')
    );

    // Add tne button to delete a choice
    addDelete(contain, deleteChoice);

    // Add the modified dataprototype to the page
    container.append(contain);

    // Get the form field to fill rows of the choices' table
    container.find('.row').each(function () {
        fillChoicesArray($(this));
    });

    // Add the delete button
    $('#newTable').find('tr:last').append('<td class="classic"></td>');
    $('#newTable').find('td:last').append(container.find('a:contains("Supprimer")'));

    // Remove the useless fileds form
    container.remove();
    tableChoices.next().remove();

    // Increase number of choices
    index++;

    whichChecked();
}

// Delete a choice
function addDelete(tr, deleteChoice) {

    // Create the button to delete a choice
    var delLink = $('<a href="#" class="btn btn-danger">'+deleteChoice+'</a>');

    // Add the button to the row
    tr.append(delLink);

    // When click, delete the matching choice's row in the table
    delLink.click(function(e) {
        $(this).parent('td').parent('tr').remove();
        e.preventDefault();
        return false;
    });
}

// Check if form is valid
function check_form(nbrChoices, answerCoched, labelEmpty, pointAnswers, pointAnswer, inviteQuestion) {
    //"use strict";

    // If no question is asked
    if ($('#ujm_exobundle_interactionqcmtype_interaction_invite').val() == '') {
        alert(inviteQuestion);
        return false;
    } else {
        // If there is no at least two choices
        if (($('#newTable').find('tr:not(:first)').length) < 2) {
            alert(nbrChoices);
            return false;
        } else {
            // If no expected answer is selected
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
                // If all the points fields are fill
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

// Set the choices order
function setOrder() {

    var order = 1;

    $('#newTable').find('tr:not(:first)').each(function () {
        $(this).find('input:first').val(order);
        order++;
    });
}

function whichChecked() {
     // Show or hide positionForce if shuffle is checked
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
    // Disable or not the score by response if weightResponse is checked
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

    // Change the type od ExpectedAnswer (radio or checkbox) depending on which typeQCM is choose
    var type = $('#ujm_exobundle_interactionqcmtype_typeQCM option:selected').val();

    $("*[id$='_rightResponse']").each(function () {
        if (type == 1) {
            $(this).prop('type', 'checkbox');
        } else {
            $(this).prop('type', 'radio');
        }
    });
}

function whichChange() {
    // When "choices shuffle" change, show position force possibility
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

    // When "set points by response" change, disable or not single choice point
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

    // When "type of QCM (unique/multiple)" change, change the expected response to radio or checkbox
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
}

function fillChoicesArray(row) {
    // Add the field of type input
    if (row.find('input').length) {
        if (row.find('input').attr('id').indexOf('ordre') == -1) {
            $('#newTable').find('tr:last').append('<td class="classic"></td>');
            $('#newTable').find('td:last').append(row.find('input'));
        } else {
            // Add the field positionForced as hidden td
            $('#newTable').find('tr:last').append('<td class="classic" style="display:none;"></td>');
            $('#newTable').find('td:last').append(row.find('input'));
        }
    }

    // Add the field of type textarea
    if (row.find('textarea').length) {
        $('#newTable').find('tr:last').append('<td class="classic"></td>');
        $('#newTable').find('td:last').append(row.find('textarea'));
    }
}

function tableChoicesCreation(expectedAnswer, response, point, comment, positionForce, addchoice, deleteChoice, nbResponses) {

    if (nbResponses == 0) {
        // Add the structure od the table
        tableChoices.append('<table id="newTable" class="table table-striped table-bordered table-condensed"><thead><tr style="background-color: lightsteelblue;"><th class="classic">'+expectedAnswer+'</th><th class="classic">'+response+'</th><th class="classic">'+point+'</th><th class="classic">'+comment+'</th><th class="classic">'+positionForce+'</th><th class="classic">-----</th></tr></thead><tbody><tr></tr></tbody></table>');

        // create the button to add a choice
        var add = $('<a href="#" id="add_choice" class="btn btn-primary"><i class="icon-plus"></i>&nbsp;'+addchoice+'</a>');

        // Add the button after the table
        tableChoices.append(add);

        // When click, add a new choice in the table
        add.click(function (e) {
            $('#newTable').find('tbody').append('<tr></tr>');
            addChoice(container, deleteChoice);
            e.preventDefault(); // prevent add # in the url
            return false;
        });
    } else {
        // Add the structure od the table
        tableChoices.append('<table id="newTable" class="table table-striped table-bordered table-condensed"><thead><tr style="background-color: lightsteelblue;"><th class="classic">'+expectedAnswer+'</th><th class="classic">'+response+'</th><th class="classic">'+point+'</th><th class="classic">'+comment+'</th><th class="classic">'+positionForce+'</th></tr></thead><tbody><tr></tr></tbody></table>');
    }
}