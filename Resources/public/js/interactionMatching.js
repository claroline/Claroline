var containerLabel = $('div#ujm_exobundle_interactionmatchingtype_labels'); // Div which contain the dataprototype of labels
var containerProposal = $('div#ujm_exobundle_interactionmatchingtype_proposals');

var tableLabels = $('#tableLabel'); // div which contain the labels array
var tableProposals = $('#tableProposal');

var typeMatching;

var advEditionLang;

// Question creation
function creationMatching(addchoice, addproposal, deletechoice, LabelValue, ScoreRight, ProposalValue, numberProposal, correspondence, deleteLabel, deleteProposal, tMatching, advEdition){

    //initialisation of variables
    var indexLabel; // number of label
    var indexProposal;
    var codeContainerLabel = 0; // to differentiate containers
    var codeContainerProposal = 1;

    advEditionLang = advEdition;

    typeMatching = JSON.parse(tMatching);

    //in the first time
    $('#ujm_exobundle_interactionmatchingtype_typeMatching').children('option').each(function() {
         if (typeMatching[$(this).val()] == 2) {
             $(this).prop('selected', true);
         } else {
             $(this).attr('disabled', 'disabled');
         }
     });

    tableCreationLabel(containerLabel, tableLabels, addchoice, deletechoice, LabelValue, ScoreRight, 0, codeContainerLabel, deleteLabel, correspondence);
    tableCreationProposal(containerProposal, tableProposals, addproposal, deletechoice, ProposalValue, 0, codeContainerProposal, deleteProposal, numberProposal);

    // Number of label initially
    indexLabel = containerLabel.find(':input').length;
    indexProposal = containerProposal.find(':input').length;

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

    // If no label exist, add two labels by default in the container Label
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
}

// Question edition
function creationMatchingEdit(){
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
                .replace('<a class="btn btn-danger remove" href="#">Delete</a>', '')
            );
    }

    adddelete(contain, deletechoice, codeContainer);
    container.append(contain);

    container.find('.row').each(function () {
        fillLabelArray();
    });

    // Add correspondence
    $('#newTableLabel').find('tr:last').append('<td class="classic"></td>');
    $('#newTableLabel').find('td:last').append('<select id="' + $('#newTableLabel').find('tr:not(:first)').length + '_correspondence" \n\
                                                name="' + $('#newTableLabel').find('tr:not(:first)').length + '_correspondence[]" \n\
                                                multiple></select>');

    $('#newTableProposal').find('tbody').find('tr').each( function() {
        rowInd = this.rowIndex;

        $("#" + $('#newTableLabel').find('tr:not(:first)').length + "_correspondence").append($('<option>', {
                                                        value: rowInd,
                                                        text:  rowInd
                                                    }));
    });

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
                .replace('<a class="btn btn-danger remove" href="#">Delete</a>', '')
            );
    }

    adddelete(contain, deletechoice, codeContainer);
    container.append(contain);

    container.find('.row').each(function () {
        fillProposalArray();
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
function check_form(nbrProposals, nbrLabels){
//    if (($('#newTableProposal').find('tr:not(:first)').length) < 2) {
//            return false;
//    }
//
//    if (($('#newTableProposal').find('tr:not(:first)').length) < ($('#newTableLabel').find('tr:not(:first)').length)) {
//            return false;
//    }

//    while(containerLabel.find(':input').length)
//    {
////        "ujm_exobundle_interactionmatchingtype_labels_1_correspondence"
//        if($()) {
//
//        }
//    }

}

function fillLabelArray() {

    // Add the field of type textarea
    if (containerLabel.find('.row').find('input').length) {
        idLabelVal = containerLabel.find('.row').find('.labelVal').attr("id");
        $('#newTableLabel').find('tr:last').append('<td class="classic"></td>');
        $('#newTableLabel').find('td:last').append(containerLabel.find('.row').find('.labelVal'));
        $('#newTableLabel').find('td:last').append('<span><a href="#" id="adve_'+idLabelVal+'">'+advEditionLang+'</a></span>');

        advLabelVal(idLabelVal);
    }

    // Add the field of type input
    if (containerLabel.find('.row').find('input').length) {
        $('#newTableLabel').find('tr:last').append('<td class="classic"></td>');
        $('#newTableLabel').find('td:last').append(containerLabel.find('.row').find('.labelScore'));
    }

    // Add the field of type select
    if (containerLabel.find('.row').find('select').length) {
        $('#newTableLabel').find('tr:last').append('<td class="classic"></td>');
        $('#newTableLabel').find('td:last').append(containerLabel.find('.row').find('select'));
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

function fillProposalArray() {

    // Add the field of type textarea
    if (containerProposal.find('.row').find('textarea').length) {
        idProposalVal = containerProposal.find('.row').find('textarea').attr("id");
        $('#newTableProposal').find('tr:last').append('<td class="classic"></td>');
        $('#newTableProposal').find('td:last').append(containerProposal.find('.row').find('textarea'));
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
            table.append('<table id="newTableLabel" class="table table-striped table-bordered table-condensed"><thead><tr style="background-color: lightsteelblue;"><th class="classic">'+LabelValue+'</th><th class="classic">'+ScoreRight+'</th><th class="classic">'+correspondence+'</th><th class="classic">'+supp+'</th></tr></thead><tbody><tr></tr></tbody></table>');
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
        table.append('<table id="newTableProposal" class="table table-striped table-bordered table-condensed"><thead><tr style="background-color: lightsteelblue;"><th class="classic">'+correspondence+'</th><th class="classic">'+ProposalValue+'</th><th class="classic">'+supp+'</th></tr></thead><tbody><tr></tr></tbody></table>');
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