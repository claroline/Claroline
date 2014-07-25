var container = $('div#ujm_exobundle_interactionmatchingtype_labels'); // Div which contain the dataprototype
var tableLabel = $('#tableLabel'); // div which contain the choices array

var typeMatching;

// Matching Creation
function creationMatching(expectedAnswer, response, point, comment, positionForce, addchoice, deleteChoice, tMatching) {

    var index; // number of choices
    
    typeMatching = JSON.parse(tMatching);

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
    $(document).on('click', ':radio', function () {
        if ($(this).is(':checked')) {
           radioJustChecked = $(this).attr("id");
           $('#newTable').find(('tr:not(:first)')).each(function () {
                if (radioJustChecked != $(this).find('input').eq(1).attr("id")) {
                    $(this).find('input').eq(1).removeAttr('checked');
                }
           });
       }
    });

    // Make the choices' table sortable with jquery ui plugin
    //$('tbody').sortable();

    // Return a helper with preserved width of cells
    var fixHelper = function(e, ui) {
        ui.children().each(function() {
                $(this).width($(this).width());
        });
        return ui;
    };

    /*$('tbody').sortable({
        helper: fixHelper,
        cancel: 'contenteditable',
        stop: function (event, ui) {
            $(ui.item).find('.claroline-tiny-mce').each(function () {
                tinyMCE.get($(this).attr('id')).remove();
                $(this).removeClass('tiny-mce-done');
                $('body').trigger('DOMSubtreeModified');
            });
        }
    });*/
}

// Matching Edition
function creationMatchingEdit(expectedAnswer, response, point, comment, positionForce, addchoice, deleteChoice, tMatching, nbResponses) {

    var index = 0;
    
    typeMatching = JSON.parse(tMatching);

    tableChoicesCreation(expectedAnswer, response, point, comment, positionForce, addchoice, deleteChoice, nbResponses);

    // Get the form field to fill rows of the choices' table
    container.children().first().children('div').each(function () {

        // Add a row to the table
        $('#newTable').find('tbody').append('<tr></tr>');

         $(this).find('.row').each(function () {

            fillChoicesArray($(this));

            // Add the form errors
            $('#labelError').append($(this).find('span'));
        });

        if (nbResponses == 0) {
            // Add the delete button
            $('#newTable').find('tr:last').append('<td class="classic"></td>');
            addDelete($('#newTable').find('td:last'), deleteChoice);
        }
        
        $('#ujm_exobundle_interactionmatchingtype_labels_'+index+'_weight').click(function() {
            $(this).focus();
        });
        
        index++;
        
    });

    // Remove the useless fields form
    container.remove();
    tableChoices.next().remove();

    whichChecked();
    whichChange();

    $(document).on('click', ':radio', function () {
        if ($(this).is(':checked')) {
           radioJustChecked = $(this).attr("id");
           $('#newTable').find(('tr:not(:first)')).each(function () {
                if (radioJustChecked != $(this).find('input').eq(1).attr("id")) {
                    $(this).find('input').eq(1).removeAttr('checked');
                }
           });
       }
    });

    // Make the choices' table sortable with jquery ui plugin
    //$('tbody').sortable();

    // Return a helper with preserved width of cells
    var fixHelper = function(e, ui) {
        ui.children().each(function() {
                $(this).width($(this).width());
        });
        return ui;
    };

    $('tbody').sortable({
        helper: fixHelper,
        cancel: 'contenteditable',
        stop: function (event, ui) {
            $(ui.item).find('.claroline-tiny-mce').each(function () {
                tinyMCE.get($(this).attr('id')).remove();
                $(this).removeClass('tiny-mce-done');
                $('body').trigger('DOMSubtreeModified');
            });
        }
    });
}

// Add a choice
function addLabel(container, deleteChoice) {
    var uniqChoiceID = false;

    var index = $('#newTable').find('tr:not(:first)').length;

    while (uniqChoiceID == false) {
        if ($('#ujm_exobundle_interactionmatchingtype_labels_' + index + '_label').length) {
            index++;
        } else {
            uniqChoiceID = true;
        }
    }

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
    $('#newTable').find('td:last').append(contain.find('a:contains("'+deleteChoice+'")'));

    // Remove the useless fileds form
    container.remove();
    tableChoices.next().remove();

    whichChecked();
    
    $('#ujm_exobundle_interactionmatchingtype_labels_'+index+'_weight').click(function() {
        $(this).focus();
    });
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

    /*if ($("*[id$='_penalty']").length > 0) {
        $("*[id$='_penalty']").val($("*[id$='_penalty']").val().replace(/[-]/, ''));
    }*/

    // If no question is asked
    if ($('#ujm_exobundle_interactionmatchingtype_interaction_invite').val() == '') {
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
                if ($('#ujm_exobundle_interactionmatchingtype_weightResponse').is(':checked')) {
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
//    // Disable or not the score by response if weightResponse is checked
//    if ($('#ujm_exobundle_interactionmatchingtype_weightResponse').is(':checked')) {
//        $('#ujm_exobundle_interactionmatchingtype_scoreRightResponse').prop('disabled', true);
//        $('#ujm_exobundle_interactionmatchingtype_scoreFalseResponse').prop('disabled', true);
//
//        $("*[id$='_weight']").each(function() {
//            $(this).prop('disabled', false);
//        });
//    } else {
//        $('#ujm_exobundle_interactionmatchingtype_scoreRightResponse').prop('disabled', false);
//        $('#ujm_exobundle_interactionmatchingtype_scoreFalseResponse').prop('disabled', false);
//
//        $("*[id$='_weight']").each(function() {
//            $(this).prop('disabled', true);
//        });
//    }
//
//    // Change the type od ExpectedAnswer (radio or checkbox) depending on which typeMatching is choose
//    var type = $('#ujm_exobundle_interactionmatchingtype_typeMatching option:selected').val();

//    $("*[id$='_rightResponse']").each(function () {
//        if (typeMatching[type] == 1) {
//            $(this).prop('type', 'checkbox');
//        } else {
//            $(this).prop('type', 'radio');
//        }
//    });
}

function whichChange() {
    // When "choices shuffle" change, show position force possibility
    $('#ujm_exobundle_interactionmatchingtype_shuffle').change(function () {
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
    $('#ujm_exobundle_interactionmatchingtype_weightResponse').change(function () {
        if ($(this).is(':checked')) {
            $('#ujm_exobundle_interactionmatchingtype_scoreRightResponse').prop('disabled', true);
            $('#ujm_exobundle_interactionmatchingtype_scoreFalseResponse').prop('disabled', true);

            $("*[id$='_weight']").each(function() {
                $(this).prop('disabled', false);
            });
        } else {
            $('#ujm_exobundle_interactionmatchingtype_scoreRightResponse').prop('disabled', false);
            $('#ujm_exobundle_interactionmatchingtype_scoreFalseResponse').prop('disabled', false);

            $("*[id$='_weight']").each(function() {
                $(this).prop('disabled', true);
            });
        }
    });

    // When "type of Matching (unique/multiple)" change, change the expected response to radio or checkbox
    $('#ujm_exobundle_interactionmatchingtype_typeMatching').change(function () {
        var type = $('#ujm_exobundle_interactionmatchingtype_typeMatching option:selected').val();

        $("*[id$='_rightResponse']").each(function () {
            if (typeMatching[type] == 1) {
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
        tableLabels.append('<table id="newTable" class="table table-striped table-bordered table-condensed"><thead><tr style="background-color: lightsteelblue;"><th class="classic">'+expectedAnswer+'</th><th class="classic">'+response+'</th><th class="classic">'+point+'</th><th class="classic">'+comment+'</th><th class="classic">'+positionForce+'</th><th class="classic">-----</th></tr></thead><tbody><tr></tr></tbody></table>');

        // create the button to add a choice
        var add = $('<a href="#" id="add_choice" class="btn btn-primary"><i class="fa fa-plus"></i>&nbsp;'+addchoice+'</a>');

        // Add the button after the table
        tableLabels.append(add);

        // When click, add a new choice in the table
        add.click(function (e) {
            $('#newTable').find('tbody').append('<tr></tr>');
            addChoice(container, deleteChoice);
            e.preventDefault(); // prevent add # in the url
            return false;
        });
    } else {
        // Add the structure od the table
        tableLabels.append('<table id="newTable" class="table table-striped table-bordered table-condensed"><thead><tr style="background-color: lightsteelblue;"><th class="classic">'+expectedAnswer+'</th><th class="classic">'+response+'</th><th class="classic">'+point+'</th><th class="classic">'+comment+'</th><th class="classic">'+positionForce+'</th></tr></thead><tbody><tr></tr></tbody></table>');
    }
}
