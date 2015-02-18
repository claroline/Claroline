var containerProposal = $('div#ujm_exobundle_interactionmatchingtype_proposals'); // Div which contain the dataprototype of proposals
var containerLabel = $('div#ujm_exobundle_interactionmatchingtype_labels'); // Div which contain the dataprototype of labels

var tableProposals = $('#tableProposal'); // div which contain the proposals array
var tableLabels = $('#tableLabel'); // div which contain the labels array

var typeMatching;

var advEditionLang;
var correspEmptyLang;
var correspErrorLang;
var scoreErrorLang;

var codeContainerProposal = 1; // to differentiate containers
var codeContainerLabel = 0;

// Question creation
function creationMatching(addchoice, addproposal, deletechoice, LabelValue, ScoreRight, ProposalValue, numberProposal, correspondence, deleteLabel, deleteProposal, tMatching, advEdition, correspEmpty, correspondenceError , scoreError){


    //initialisation of variables
    var indexProposal;
    var indexLabel; // number of label

    advEditionLang = advEdition;
    correspEmptyLang = correspEmpty;
    correspErrorLang = correspondenceError;
    scoreErrorLang = scoreError;

    typeMatching = JSON.parse(tMatching);

    //in the first time
    $('#ujm_exobundle_interactionmatchingtype_typeMatching').children('option').each(function() {
         if (typeMatching[$(this).val()] == 2) {
             $(this).prop('selected', true);
         } else {
             $(this).attr('disabled', 'disabled');
         }
     });

    tableCreationProposal(containerProposal, tableProposals, addproposal, deletechoice, ProposalValue, 0, codeContainerProposal, deleteProposal, numberProposal);
    tableCreationLabel(containerLabel, tableLabels, addchoice, deletechoice, LabelValue, ScoreRight, 0, codeContainerLabel, deleteLabel, correspondence);


    // Number of label initially
    indexProposal = containerProposal.find(':input').length;
    indexLabel = containerLabel.find(':input').length;

    // If no proposal exist, add two labels by default in the container Label
    if (indexProposal == 0) {
        addProposal(containerProposal, deletechoice, tableProposals, codeContainerProposal);
        $('#newTableProposal').find('tbody').append('<tr><td></td></tr>');
        addProposal(containerProposal, deletechoice, tableProposals, codeContainerProposal);
    // If label already exist, add button to delete it
    } else {
        tableProposals.children('tr').each(function() {
            adddelete($(this), deletechoice, codeContainerProposal);
        });
    }

    // If no label exist, add two labels by default in the container Label
    if (indexLabel == 0) {
        addLabel(containerLabel, deletechoice, tableLabels, codeContainerLabel);
        $('#newTableLabel').find('tbody').append('<tr></tr>');
        addLabel(containerLabel, deletechoice, tableLabels, codeContainerLabel);
    // If label already exist, add button to delete it
    } else {
        tableLabels.children('tr').each(function() {
            adddelete($(this), deletechoice, codeContainerLabel);
        });
    }
}

// Question edition
function creationMatchingEdit(addchoice, addproposal, deletechoice, LabelValue, ScoreRight, ProposalValue, numberProposal, correspondence, deleteLabel, deleteProposal, tMatching, advEdition, correspEmpty, nbResponses, valueCorrespondence, tableLabel, tableProposal, correspondenceError, scoreError) {

    typeMatching = JSON.parse(tMatching);
    var valueCorres = JSON.parse(valueCorrespondence.replace(/&quot;/ig,'"'));
    var labels = JSON.parse(tableLabel.replace(/&quot;/ig,'"'));
    var proposals = JSON.parse(tableProposal.replace(/&quot;/ig,'"'));
    var ind = 1;

    advEditionLang = advEdition;
    correspEmptyLang = correspEmpty;
    correspErrorLang = correspondenceError;
    scoreErrorLang = scoreError;

    //in the first time
    $('#ujm_exobundle_interactionmatchingtype_typeMatching').children('option').each(function() {
        if (typeMatching[$(this).val()] == 2) {
            $(this).prop('selected', true);
        } else {
            $(this).attr('disabled', 'disabled');
        }
    });

    tableCreationProposal(containerProposal, tableProposals, addproposal, deletechoice, ProposalValue, nbResponses, codeContainerProposal, deleteProposal, numberProposal);
    tableCreationLabel(containerLabel, tableLabels, addchoice, deletechoice, LabelValue, ScoreRight, nbResponses, codeContainerLabel, deleteLabel, correspondence);

    containerProposal.children().first().children('div').each(function() {

        $(this).find('.row').each(function() {
            
            fillProposalArray($(this));

            //uncode chevrons
            $('.classic').find('textarea').each(function() {
                $(this).val($(this).val().replace("&lt;", "<"));
                $(this).val($(this).val().replace("&gt;", ">"));
            });

            addRemoveRowTableProposal();

            // Add the form errors
            $('#proposalError').append($(this).find('span'));
        });

        if (nbResponses == 0) {

            // Add the delete button
            $('#newTableProposal').find('tr:last').append('<td class="classic"></td>');
            adddelete($('#newTableProposal').find('td:last'), deletechoice);
        }

        $('#newTableProposal').find('tbody').append('<tr><td></td></tr>');
    });
    $('#newTableProposal').find('tr').last().remove();

    containerProposal.remove();
    tableProposals.next().remove();

    containerLabel.children().first().children('div').each(function() {

        $(this).find('.row').each(function() {

            fillLabelArray($(this));

            $('.classic').find('textarea').each(function() {
                $(this).val($(this).val().replace("&lt;", "<"));
                $(this).val($(this).val().replace("&gt;", ">"));
            });

            // Add the form errors
            $('#labelError').append($(this).find('.field-error'));
        });

        // add correspondence
        addCorrespondence();

        if (nbResponses == 0) {
            // Add the delete button
            $('#newTableLabel').find('tr:last').append('<td class="classic"></td>');
            adddelete($('#newTableLabel').find('td:last'), deletechoice);
        }

        $('#newTableLabel').find('tbody').append('<tr></tr>');

        if (typeof labels[ind] !== 'undefined') {
            idlabel = labels[ind];
            idproposals = valueCorres[idlabel];
            $.each( idproposals, function(key, val){//alert(proposals[val]);
                $('#' + ind + '_correspondence option[value="' + proposals[val] + '"]').prop('selected', true);
            });
        }

        ind++;
    });
    
    //for activate tinymce if there is html balise
    $('.classic').find('textarea').each(function() {
        if($(this).val().match("<p>")) {
            idProposalVal = $(this).attr("id");
            $("#"+idProposalVal).addClass("claroline-tiny-mce hide");
            $("#"+idProposalVal).data("data-theme","advanced");
        }
    });
    
    $('#newTableLabel').find('tr').last().remove();
    containerLabel.remove();
    tableLabels.next().remove();
}

function addLabel(container, deletechoice, table, codeContainer){
    var contain;
    var uniqLabelId = false;
    var indexLabel = $('#newTableLabel').find('tr:not(:first)').length;
    while (uniqLabelId == false) {
        if ($('#ujm_exobundle_interactionmatchingtype_labels_' + indexLabel + '_scoreRightResponse').length){
                indexLabel++;
            } else{
                uniqLabelId = true;
            }
            // Change the "name" by the index and delete the symfony delete form button
            contain = $(container.attr('data-prototype').replace(/__name__label__/g, 'Choice n°' + (indexLabel))
                .replace(/__name__/g, indexLabel)
                .replace('<a class="btn btn-danger remove" href="#"><i class="fa fa-close"></i></a>', '')
            );
    }

    adddelete(contain, deletechoice, codeContainer);
    container.append(contain);

    container.find('.row').each(function () {
        fillLabelArray($(this));
    });

    // Add correspondence
    addCorrespondence();

    // Add the delete button
    $('#newTableLabel').find('tr:last').append('<td class="classic"></td>');
    $('#newTableLabel').find('td:last').append(contain.find('a:contains("' + deletechoice + '")'));

    // Remove the useless fileds form
    container.remove();
    table.next().remove();
}

function addProposal(container, deletechoice, table, codeContainer){
    var contain;
    var uniqProposalId = false;
    var indexProposal = $('#newTableProposal').find('tr:not(:first)').length;
    while (uniqProposalId == false) {
        if ($('#ujm_exobundle_interactionmatchingtype_proposals_' + indexProposal + '_value').length){
                indexProposal++;
            } else{
                uniqProposalId = true;
            }
            // Change the "name" by the index and delete the symfony delete form button
            contain = $(container.attr('data-prototype').replace(/__name__label__/g, 'Choice n°' + (indexProposal))
                .replace(/__name__/g, indexProposal)
                .replace('<a class="btn btn-danger remove" href="#"><i class="fa fa-close"></i></a>', '')
            );
    }

    adddelete(contain, deletechoice, codeContainer);
    container.append(contain);

    container.find('.row').each(function () {
        fillProposalArray($(this));
    });

    // Add the delete button
    $('#newTableProposal').find('tr:last').append('<td class="classic"></td>');
    $('#newTableProposal').find('td:last').append(contain.find('a:contains("' + deletechoice + '")'));

    // Remove the useless fileds form
    container.remove();
    table.next().remove();

    addRemoveRowTableProposal();
}

//check if the form is valid
function check_form(nbrProposals, nbrLabels) {
    var correspondence = false;
    var proposalSelected = [];
    var singleProposal = true;
    var score = true;

    if (($('#newTableProposal').find('tr:not(:first)').length) < 1) {

        alert(nbrProposals);
        return false;
    }

    if (($('#newTableLabel').find('tr:not(:first)').length) < 1) {

        alert(nbrLabels);
        return false;
    }

    $("*[id$='scoreRightResponse']").each( function() {

          if(!(parseFloat($(this).val()) == parseInt($(this).val())) && isNaN($(this).val())){

            alert(scoreErrorLang);
            score = false;
        }
    });

    if(score == false ){

        return false
    }

    $("*[id$='_correspondence']").each( function() {
        if ($("option:selected", this).length > 0) {
            correspondence = true;
            $("option:selected", this).each( function () {
                //alert($(this).val());
                //si dans tableau return false + mmsg si non ajout dans tableau
                if (proposalSelected[$(this).val()]) {
                    alert(correspErrorLang);
                    singleProposal = false;
                } else {
                    proposalSelected[$(this).val()] = true;
                }
            });
        }
    });

    if (singleProposal == false) {

        return false;
    }

    if (correspondence == false) {

        return confirm(correspEmptyLang);
    }

    //for encoding the chevrons
    $('.classic').find('textarea:visible').each(function() {
        $(this).val($(this).val().replace("<", "&lt;"));
        $(this).val($(this).val().replace(">", "&gt;"));
    });
}

function fillLabelArray(row) {

    // Add the field of type textarea
    if (row.find('textarea').length) {
        idLabelVal = row.find('textarea').attr("id");
        $('#newTableLabel').find('tr:last').append('<td class="classic"></td>');
        $('#newTableLabel').find('td:last').append(row.find('textarea'));
        $('#newTableLabel').find('td:last').append('<span><a href="#" id="adve_'+idLabelVal+'">'+advEditionLang+'</a></span>');

        advLabelVal(idLabelVal);
    }

    // Add the field of type input
    if (row.find('input').length) {
        $('#newTableLabel').find('tr:last').append('<td class="classic"></td>');
        $('#newTableLabel').find('td:last').append(row.find('.labelScore'));
    }

    // Add the field of type select
    if (row.find('select').length) {
        $('#newTableLabel').find('tr:last').append('<td class="classic"></td>');
        $('#newTableLabel').find('td:last').append(row.find('select'));
    }
}

function advLabelVal(idLabelVal) {
    $("#adve_"+idLabelVal).click(function(e) {
            if ($("#"+idLabelVal).hasClass("claroline-tiny-mce hide")) {
//                $("#"+idLabelVal).removeClass("claroline-tiny-mce");
//                $("#"+idLabelVal).removeClass("hide");
//                $("#"+idLabelVal).removeData("data-theme");

            } else {
                $("#"+idLabelVal).addClass("claroline-tiny-mce hide");
                $("#"+idLabelVal).data("data-theme","advanced");
            }

            e.preventDefault();
            return false;
        });
}

function fillProposalArray(row) {

    // Add the field of type textarea
    if (row.find('textarea').length) {
        idProposalVal = row.find('textarea').attr("id");
        $('#newTableProposal').find('tr:last').append('<td class="classic"></td>');
        $('#newTableProposal').find('td:last').append(row.find('textarea'));
        $('#newTableProposal').find('td:last').append('<span><a href="#" id="adve_'+idProposalVal+'">'+advEditionLang+'</a></span>');

        advProposalVal(idProposalVal);
    }

}

function advProposalVal(idProposalVal) {
    $("#adve_"+idProposalVal).click(function(e) {
            if ($("#"+idProposalVal).hasClass("claroline-tiny-mce hide")) {
                //todo

            } else {
                $("#"+idProposalVal).addClass("claroline-tiny-mce hide");
                $("#"+idProposalVal).data("data-theme","advanced");
            }

            e.preventDefault();
            return false;
        });
}

function adddelete(tr, deletechoice, codeContainer){
    var delLink;
    // Create the button to delete a row
    if(codeContainer == 0){
        delLink = $('<a href="newTableLabel" class="btn btn-danger">' + deletechoice + '</a>');
    } else {
        delLink = $('<a href="newTableProposal" class="btn btn-danger">' + deletechoice + '</a>');
    }

    // Add the button to the row
    tr.append(delLink);

    // When click, delete the row in the table
    delLink.click(function(e) {
        $(this).parent('td').parent('tr').remove();

        addRemoveRowTableProposal();
        removeRowTableLabel();

        e.preventDefault();
        return false;
    });
}

function tableCreationLabel(container, table, button, deletechoice, LabelValue, ScoreRight, nbResponses, codeContainer, supp, correspondence){
    if (nbResponses == 0) {
        // Creation of the table
        table.append('<table id="newTableLabel" class="table table-striped table-bordered table-condensed"><thead><tr style="background-color: lightsteelblue;"><th class="classic">'+LabelValue+'</th><th class="classic">'+ScoreRight+'</th><th class="classic">'+correspondence+'</th><th class="classic">'+supp+'</th></tr></thead><tbody><tr></tr></tbody></table>');

        // Creation of the button add
        var add = $('<a href="#" id="add_label" class="btn btn-primary"><i class="fa fa-plus"></i>&nbsp;'+button+'</a>');

        // Add the button add
        table.append(add);
        add.click(function (e) {
            $('#newTableLabel').find('tbody').append('<tr></tr>');
            addLabel(container, deletechoice, table, codeContainer);
            e.preventDefault(); // prevent add # in the url
            return false;
        });
    } else {
        // Add the structure of the table
            table.append('<table id="newTableLabel" class="table table-striped table-bordered table-condensed"><thead><tr style="background-color: lightsteelblue;"><th class="classic">' + LabelValue + '</th><th class="classic">' + ScoreRight + '</th><th class="classic">' + correspondence + '</th></tr></thead><tbody><tr></tr></tbody></table>');
    }
}

function tableCreationProposal(container, table, button, deletechoice, ProposalValue, nbResponses, codeContainer, supp, correspondence){
    if (nbResponses == 0) {
        // Creation of the table
        table.append('<table id="newTableProposal" class="table table-striped table-bordered table-condensed"><thead><tr style="background-color: lightsteelblue;"><th class="classic">'+correspondence+'</th><th class="classic">'+ProposalValue+'</th><th class="classic">'+supp+'</th></tr></thead><tbody><tr><td></td></tr></tbody></table>');

        // Creation of the button add
        var add = $('<a href="#" id="add_proposal" class="btn btn-primary"><i class="fa fa-plus"></i>&nbsp;'+button+'</a>');

        // Add the button add
        table.append(add);
        add.click(function (e) {
            $('#newTableProposal').find('tbody').append('<tr><td></td></tr>');
            addProposal(container, deletechoice, table, codeContainer);
            e.preventDefault(); // prevent add # in the url
            return false;
        });
    } else {
        // Add the structure of the table
        table.append('<table id="newTableProposal" class="table table-striped table-bordered table-condensed"><thead><tr style="background-color: lightsteelblue;"><th class="classic">' + correspondence + '</th><th class="classic">' + ProposalValue + '</th></tr></thead><tbody><tr><td></td></tr></tbody></table>');
    }
}

function addRemoveRowTableProposal () {

    var rowInd;

    $("*[id$='_correspondence']").each( function() {
        $(this).find('option').remove();
    });

    $('#newTableProposal').find('tbody').find('tr').each( function() {
        rowInd = this.rowIndex;
        $(this).find('td:first').children().remove();
        $(this).find('td:first').append('<span>' + rowInd + '</span>');

        $("*[id$='_correspondence']").each( function() {
            $(this).append($('<option>', {
                            value: rowInd,
                            text:  rowInd
                        }));
        });

    });
}

function removeRowTableLabel() {
    var ind = 1;
    $("*[id$='_correspondence']").each( function() {
         $(this).attr("id", ind + "_correspondence");
         $(this).attr("name", ind + "_correspondence[]");
         ind++;
    });
}

function addCorrespondence() {

    // Add correspondence
    $('#newTableLabel').find('tr:last').append('<td class="classic"></td>');
    $('#newTableLabel').find('td:last').append('<select id="' + $('#newTableLabel').find('tr:not(:first)').length + '_correspondence" \n\
                                                name="' + $('#newTableLabel').find('tr:not(:first)').length + '_correspondence[]" \n\
                                                multiple></select>');

    $('#newTableProposal').find('tbody').find('tr').each(function() {
        rowInd = this.rowIndex;

        $("#" + $('#newTableLabel').find('tr:not(:first)').length + "_correspondence").append($('<option>', {
            value: rowInd,
            text: rowInd
        }));
    });
}
