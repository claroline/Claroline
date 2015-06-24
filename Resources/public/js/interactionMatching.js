var containerProposal = $('div#ujm_exobundle_interactionmatchingtype_proposals'); // Div which contain the dataprototype of proposals
var containerLabel = $('div#ujm_exobundle_interactionmatchingtype_labels'); // Div which contain the dataprototype of labels

var tableProposals = $('#tableProposal'); // div which contain the proposals array
var tableLabels = $('#tableLabel'); // div which contain the labels array

var typeMatching;

var advEditionLang;
var remAdvEditionLang;
var correspEmptyLang;
var correspErrorLang;
var scoreErrorLang;

var codeContainerProposal = 1; // to differentiate containers
var codeContainerLabel = 0;

var correspondances = [];

// Question creation
function creationMatching(addchoice, addproposal, deletechoice, LabelValue, ScoreRight, ProposalValue, numberProposal, correspondence, tMatching, advEdition, remAdvEdition, correspEmpty, correspondenceError , scoreError, ProposalForcePosition, LabelForcePosition, edition) {

    //initialisation of variables
    var indexProposal;
    var indexLabel; // number of label

    advEditionLang = advEdition;
    remAdvEditionLang = remAdvEdition;
    correspEmptyLang = correspEmpty;
    correspErrorLang = correspondenceError;
    scoreErrorLang = scoreError;

    typeMatching = JSON.parse(tMatching);

    tableCreationProposal(containerProposal, tableProposals, addproposal, deletechoice, ProposalValue, 0, codeContainerProposal, numberProposal, ProposalForcePosition, edition);
    tableCreationLabel(containerLabel, tableLabels, addchoice, deletechoice, LabelValue, ScoreRight, 0, codeContainerLabel, correspondence, LabelForcePosition, edition);

    // Number of label initially
    indexProposal = containerProposal.find(':input').length;
    indexLabel = containerLabel.find(':input').length;

    // If no proposal exist, add two labels by default in the container Label
    if (indexProposal == 0) {
        addProposal(containerProposal, deletechoice, tableProposals, codeContainerProposal, edition);
        $('#newTableProposal').find('tbody').append('<tr><td></td></tr>');
        addProposal(containerProposal, deletechoice, tableProposals, codeContainerProposal, edition);
    // If label already exist, add button to delete it
    } else {
        tableProposals.children('tr').each(function() {
            adddelete($(this), deletechoice, codeContainerProposal);
            //           $(this).append('<td class="classic origin"></td>');
        });
    }

    // If no label exist, add two labels by default in the container Label
    if (indexLabel == 0) {
        addLabel(containerLabel, deletechoice, tableLabels, codeContainerLabel, edition);
        $('#newTableLabel').find('tbody').append('<tr class="droppable" ></tr>');
        addLabel(containerLabel, deletechoice, tableLabels, codeContainerLabel, edition);
    // If label already exist, add button to delete it
    } else {
        tableLabels.children('tr').each(function() {
            adddelete($(this), deletechoice, codeContainerLabel);
        });
    }

    whichChange();
}

// Question edition
function creationMatchingEdit(addchoice, addproposal, deletechoice, LabelValue, ScoreRight, ProposalValue, numberProposal, correspondence, tMatching, advEdition, remAdvEdition, correspEmpty, nbResponses, valueCorrespondence, tableLabel, tableProposal, correspondenceError, scoreError, ProposalForcePosition, LabelForcePosition, edition) {

    var indexLabel = 0;
    var indexProposal = 0;
    typeMatching = JSON.parse(tMatching);
    var valueCorres = JSON.parse(valueCorrespondence.replace(/&quot;/ig,'"'));
    var labels = JSON.parse(tableLabel.replace(/&quot;/ig,'"'));
    var proposals = JSON.parse(tableProposal.replace(/&quot;/ig,'"'));
    var ind = 1;

    advEditionLang = advEdition;
    remAdvEditionLang = remAdvEdition;
    correspEmptyLang = correspEmpty;
    correspErrorLang = correspondenceError;
    scoreErrorLang = scoreError;

    tableCreationProposal(containerProposal, tableProposals, addproposal, deletechoice, ProposalValue, nbResponses, codeContainerProposal, numberProposal, ProposalForcePosition, edition);
    tableCreationLabel(containerLabel, tableLabels, addchoice, deletechoice, LabelValue, ScoreRight, nbResponses, codeContainerLabel, correspondence, LabelForcePosition, edition);

    containerProposal.children().first().children('div').each(function() {

        $(this).find('.row').each(function() {

            fillProposalArray($(this), indexProposal, edition);

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
            adddelete($('#newTableProposal').find('td:last'), deletechoice, 1);
            //            $('#newTableProposal').find('tr:last').append('<td class="classic origin"></td>');
        }

        $('#newTableProposal').find('tbody').append('<tr><td></td></tr>');
        indexProposal++;
    });
    $('#newTableProposal').find('tr').last().remove();

    containerProposal.remove();
    tableProposals.next().remove();

    containerLabel.children().first().children('div').each(function() {

        $(this).find('.row').each(function() {

            fillLabelArray($(this), indexLabel, edition);
            $('.classic').find('textarea').each(function() {
                $(this).val($(this).val().replace("&lt;", "<"));
                $(this).val($(this).val().replace("&gt;", ">"));
            });

            // Add the form errors
            $('#labelError').append($(this).find('.field-error'));
        });

        //for replace the correspondance field of the form, by our field
        $('#newTableLabel').find("select").each(function () {
            $id = $(this).attr("class");
            $parent = $(this).parent();
            if($id == "form-control") {
                $(this).remove();
                addCorrespondence($parent);
            }
        });

        if (nbResponses == 0) {
            // Add the delete button
            $('#newTableLabel').find('tr:last').append('<td class="classic"></td>');
            adddelete($('#newTableLabel').find('td:last'), deletechoice, 0);
        }

//        $('#newTableLabel').find('tbody').append('<tr class="droppable"></tr>');
        $('#newTableLabel').find('tbody').append('<tr></tr>');

        if (typeof labels[ind] !== 'undefined') {
            idlabel = labels[ind];
            idproposals = valueCorres[idlabel];
            $.each( idproposals, function(key, val) {//alert(proposals[val]);
                $('#' + ind + '_correspondence option[value="' + proposals[val] + '"]').prop('selected', true);
            });
        }

        //        if (typeof labels[ind] !== 'undefined') {
//            var idLabel = labels[ind];
//            var idProposal = valueCorres[idLabel];
//            $.each( idProposal, function(key, val) {//alert(proposals[val]);
////                $('#' + ind + '_correspondence option[value="' + proposals[val] + '"]').prop('selected', true);
//                var idPropJsPlumb = "jsPlumb_1_" + idProposal;
//                alert(labels.length);
////                placeProposal(idlabel, idPropJsPlumb);
//            });
//        }

        ind++;
        indexLabel++;
    });

    //for activate tinymce if there is html balise
    $('.classic').find('textarea').each(function() {
        //if there is at the start an open tag and a close at the end. And at the middle all caracters possible or nothing
        if($(this).val().match("<.+>.+|\s<\/.+>$")) {
            idProposalVal = $(this).attr("id");
            $("#"+idProposalVal).addClass("claroline-tiny-mce hide");
            $("#"+idProposalVal).data("data-theme","advanced");
        }
    });

    $('#newTableLabel').find('tr').last().remove();
    containerLabel.remove();
    tableLabels.next().remove();

    whichChange();
    whichChecked();

//    responseBind();
}

function addLabel(container, deletechoice, table, codeContainer, edition) {

    var contain;
    var uniqLabelId = false;
    var indexLabel = $('#newTableLabel').find('tr:not(:first)').length;
    while (uniqLabelId == false) {
        if ($('#ujm_exobundle_interactionmatchingtype_labels_' + indexLabel + '_scoreRightResponse').length) {
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
        fillLabelArray($(this), indexLabel, edition);
    });

    //for replace the correspondance field of the form, by our field
    $('#newTableLabel').find("select").each(function () {
        $id = $(this).attr("class");
        $parent = $(this).parent();
        if($id == "form-control") {
            $(this).remove();
            addCorrespondence($parent);
        }
    });

    // Add the delete button
    $('#newTableLabel').find('tr:last').append('<td class="classic"></td>');
    $('#newTableLabel').find('td:last').append(contain.find('a.btn-danger'));

    // Remove the useless fileds form
    container.remove();
    table.next().remove();

    whichChecked();

//    responseBind();
}

function addProposal(container, deletechoice, table, codeContainer, edition) {

    // for getting correspondances
    getCorrespondances();

    var contain;
    var uniqProposalId = false;
    var indexProposal = $('#newTableProposal').find('tr:not(:first)').length;
    while (uniqProposalId == false) {
        if ($('#ujm_exobundle_interactionmatchingtype_proposals_' + indexProposal + '_value').length) {
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
        fillProposalArray($(this), indexProposal, edition);
    });

    // Add the delete button
    $('#newTableProposal').find('tr:last').append('<td class="classic"></td>');
    $('#newTableProposal').find('td:last').append(contain.find('a.btn-danger'));
    //    $('#newTableProposal').find('tr:last').append('<td class="classic origin"></td>');

    // Remove the useless fileds form
    container.remove();
    table.next().remove();

    addRemoveRowTableProposal();

    // for replace correspondances
    $("#newTableLabel").find("select").each(function() {
        var numberId = $(this).attr("id");
        numberId = numberId.replace("_correspondence", "");
        for(var i = 1; i < correspondances.length; i++) {
            if (i == numberId) {
                var value = correspondances[i] + '';
                var tableau = value.split(",");
                for(var u = 0; u < tableau.length; u++) {
                    $('#'+ i + '_correspondence option[value="' + tableau[u] + '"]').prop('selected',true);
                }
            }
        }
    });

    whichChecked();

//    responseBind();
}

//check if the form is valid
function check_form(nbrProposals, nbrLabels) {
    var correspondence = false;
    var proposalSelected = [];
    var singleProposal = true;
    var score = true;

    var typeMatching = $('#ujm_exobundle_interactionmatchingtype_typeMatching option:selected').val();

    if (($('#newTableProposal').find('tr:not(:first)').length) < 1) {

        alert(nbrProposals);
        return false;
    }

    if (($('#newTableLabel').find('tr:not(:first)').length) < 1) {

        alert(nbrLabels);
        return false;
    }

    //for encoding the chevrons
    $('.classic').find('textarea:visible').each(function() {
        $(this).val($(this).val().replace("<", "&lt;"));
        $(this).val($(this).val().replace(">", "&gt;"));
    });

    $("*[id$='scoreRightResponse']").each( function() {

          if(!(parseFloat($(this).val()) == parseInt($(this).val())) && isNaN($(this).val())) {

            alert(scoreErrorLang);
            score = false;
        }
    });

    if(score == false ) {

        return false
    }

    $("*[id$='_correspondence']").each( function() {
        if ($("option:selected", this).length > 0) {
            correspondence = true;
            if (typeMatching == 2) {
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
        }
    });

    if (singleProposal == false) {

        return false;
    }

    if (correspondence == false) {

        alert(correspEmptyLang);
        return  false;
    }
}

function fillLabelArray(row, index, edition) {

    // Add the field of type textarea
    if (row.find('textarea').length) {
        idLabelVal = row.find('textarea').attr("id");
        $('#newTableLabel').find('tr:last').append('<td class="classic"></td>');
        $('#newTableLabel').find('td:last').append('<span id="spanLabel_'+idLabelVal+'" class="input-group"></span>');
        $('#spanLabel_'+idLabelVal+'').append(row.find('textarea'));
        $('#spanLabel_'+idLabelVal).append('<span class="input-group-btn"><a class="btn btn-default" id="btnEdition_'+idLabelVal+'" onClick="advancedEdition(\'ujm_exobundle_interactionmatchingtype_labels_'+index+'_value\',\'btnEdition_'+idLabelVal+'\',event);" title="'+edition+'"><i class="fa fa-font"></i></a></span>');

    }

    // Add the field of type input
    if (row.find('input').length) {
        if (row.find('input').attr('id').indexOf('ordre') == -1) {
            $('#newTableLabel').find('tr:last').append('<td class="classic"></td>');
            $('#newTableLabel').find('td:last').append(row.find('input'));
        } else {
            $('#newTableLabel').find('tr:last').append('<td class="classic" style="display:none;"></td>');
            $('#newTableLabel').find('td:last').append(row.find('input'));
        }
    }

    // Add the field of type select
    if (row.find('select').length) {
        $('#newTableLabel').find('tr:last').append('<td class="classic"></td>');
        $('#newTableLabel').find('td:last').append(row.find('select'));
    }
}

function fillProposalArray(row, index, edition) {

    // Add the field of type textarea
    if (row.find('textarea').length) {
        idProposalVal = row.find('textarea').attr("id");
        $('#newTableProposal').find('tr:last').append('<td class="classic"></td>');
        $('#newTableProposal').find('td:last').append('<span id="spanProposal_'+idProposalVal+'" class="input-group"></span>');
        $('#spanProposal_'+idProposalVal+'').append(row.find('textarea'));
        $('#spanProposal_'+idProposalVal).append('<span class="input-group-btn"><a class="btn btn-default" id="btnEdition_'+idProposalVal+'" onClick="advancedEdition(\'ujm_exobundle_interactionmatchingtype_proposals_'+index+'_value\',\'btnEdition_'+idProposalVal+'\',event);" title="'+edition+'"><i class="fa fa-font"></i></a></span>');

    }

    if(row.find('input').length) {
        if (row.find('input').attr('id').indexOf('ordre') == -1) {
            $('#newTableProposal').find('tr:last').append('<td class="classic"></td>');
            $('#newTableProposal').find('td:last').append(row.find('input'));
        } else {
            $('#newTableProposal').find('tr:last').append('<td class="classic" style="display:none;"></td>');
            $('#newTableProposal').find('td:last').append(row.find('input'));
        }
    }

}

function adddelete(tr, deletechoice, codeContainer) {
    var delLink;
    // Create the button to delete a row
    if(codeContainer == 0) {
        delLink = $('<a title="newTableLabel" href="#" class="btn btn-danger"><i class="fa fa-close"></i></a>');
    } else {
        delLink = $('<a title="newTableProposal" href="#" class="btn btn-danger"><i class="fa fa-close"></i></a>');
    }

    // Add the button to the row
    tr.append(delLink);

    // When click, delete the row in the table
    delLink.click(function(e) {
        // for getting correspondances
        getCorrespondances();
        // for update correspondances
        var numberId;
        var typeDelete = delLink.attr("title");
        if(typeDelete == "newTableLabel") {
            numberId = $(this).parent('td').parent('tr').find("select").attr("id");
            numberId = numberId.replace("_correspondence", "");
            for(var i = 1; i < correspondances.length; i++ ) {
                if(numberId == i) {
                    correspondances[i] = 0;
                }
                if(i > numberId) {
                    var w = i - 1;
                    correspondances[w] = correspondances[i];
                }
            }
        } else {
            numberId = $(this).parent('td').parent('tr').find("span").text();
            numberId = numberId.replace("Edition avancée", "");
            for(var i = 1; i < correspondances.length; i++ ) {
                var value = correspondances[i] + '';
                var tableau = value.split(",");
                for(var u = 0; u < tableau.length; u++ ) {
                    if(tableau[u] == numberId) {
                        tableau[u] = 0;
                    }
                    if(tableau[u] > numberId) {
                        tableau[u] = tableau[u] -1;
                    }
                }
                correspondances[i] = tableau;
            }
        }

        $(this).parent('td').parent('tr').remove();

        addRemoveRowTableProposal();
        removeRowTableLabel();

        // for replace correspondances
        $("#newTableLabel").find("select").each(function() {
            for(var i = 1; i < correspondances.length; i++ ) {
                var value = correspondances[i] + '';
                var tableau = value.split(",");
                for(var u = 0; u < tableau.length; u++) {
                    $('#'+ i + '_correspondence option[value="' + tableau[u] + '"]').prop('selected',true);
                }
            }
        });

        e.preventDefault();
        return false;
    });
}

function tableCreationLabel(container, table, button, deletechoice, LabelValue, ScoreRight, nbResponses, codeContainer, correspondence,forcePosition, edition) {
    if (nbResponses == 0) {
        // Creation of the table
        table.append('<table id="newTableLabel" class="table table-striped table-bordered table-condensed"><thead><tr style="background-color: lightsteelblue;"><th class="classic">'+LabelValue+'</th><th class="classic">'+ScoreRight+'</th><th class="classic">'+correspondence+'</th><th class="classic">'+forcePosition+'</th><th class="classic">'+deletechoice+'</th></tr></thead><tbody><tr></tr></tbody></table>');

        // Creation of the button add
        var add = $('<a href="#" id="add_label" class="btn btn-primary"><i class="fa fa-plus"></i>&nbsp;'+button+'</a>');

        // Add the button add
        table.append(add);
        add.click(function (e) {
            $('#newTableLabel').find('tbody').append('<tr class="droppable"></tr>');
            addLabel(container, deletechoice, table, codeContainer, edition);
//            replaceConnections();
            e.preventDefault(); // prevent add # in the url
            return false;
        });
    } else {
        // Add the structure of the table
        table.append('<table id="newTableLabel" class="table table-striped table-bordered table-condensed"><thead><tr style="background-color: lightsteelblue;"><th class="classic">'+LabelValue+'</th><th class="classic">'+ScoreRight+'</th><th class="classic">'+correspondence+'</th><th class="classic">'+forcePosition+'</th></tr></thead><tbody><tr></tr></tbody></table>');
    }
}

function tableCreationProposal(container, table, button, deletechoice, ProposalValue, nbResponses, codeContainer, correspondence, forcePosition, edition) {
    if (nbResponses == 0) {
        // Creation of the table
        table.append('<table id="newTableProposal" class="table table-striped table-bordered table-condensed"><thead><tr style="background-color: lightsteelblue;"><th class="classic">'+correspondence+'</th><th class="classic">'+ProposalValue+'</th><th class="classic">'+forcePosition+'</th><th class="classic">'+deletechoice+'</th></tr></thead><tbody><tr><td></td></tr></tbody></table>');

        // Creation of the button add
        var add = $('<a href="#" id="add_proposal" class="btn btn-primary"><i class="fa fa-plus"></i>&nbsp;'+button+'</a>');

        // Add the button add
        table.append(add);
        add.click(function (e) {
            $('#newTableProposal').find('tbody').append('<tr><td></td></tr>');
            addProposal(container, deletechoice, table, codeContainer, edition);
//            replaceConnections();
            e.preventDefault(); // prevent add # in the url
            return false;
        });
    } else {
        // Add the structure of the table
       table.append('<table id="newTableProposal" class="table table-striped table-bordered table-condensed"><thead><tr style="background-color: lightsteelblue;"><th class="classic">'+correspondence+'</th><th class="classic">'+ProposalValue+'</th><th class="classic">'+forcePosition+'</th></tr></thead><tbody><tr><td></td></tr></tbody></table>');
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

function addCorrespondence($parent) {
    $parent.append('<select id="' + $('#newTableLabel').find('tr:not(:first)').length + '_correspondence" \n\
                                                name="' + $('#newTableLabel').find('tr:not(:first)').length + '_correspondence[]" \n\
                                                multiple></select>');
    $parent.find('select').height('40');

    $('#newTableProposal').find('tbody').find('tr').each(function() {
        rowInd = this.rowIndex;

        $("#" + $('#newTableLabel').find('tr:not(:first)').length + "_correspondence").append($('<option>', {
            value: rowInd,
            text: rowInd
        }));
    });
}

function getCorrespondances() {
    $("#newTableLabel").find("select").each(function() {
        var numberId = $(this).attr("id");
        numberId = numberId.replace("_correspondence", "");
        var selected = $(this).val();
        correspondances[numberId] = selected;
    });
}

function whichChange() {
    $("#ujm_exobundle_interactionmatchingtype_shuffle").change(function() {
        if ($(this).is(':checked')) {
            tableProposals.find('th').eq(2).show();
            tableLabels.find('th').eq(3).show();
            $("*[id$='_positionForce']").each(function () {
                $(this).parent('td').show();
            });
        } else {
            tableProposals.find('th').eq(2).hide();
            tableLabels.find('th').eq(3).hide();
            $("*[id$='_positionForce']").each(function () {
               $(this).parent('td').hide();
           });
        }
    });
}

function whichChecked() {
     // Show or hide positionForce if shuffle is checked
    if ($('#ujm_exobundle_interactionmatchingtype_shuffle').is(':checked')) {
        tableProposals.find('th').eq(2).show();
        tableLabels.find('th').eq(3).show();
        $("*[id$='_positionForce']").each(function () {
            $(this).parent('td').show();
        });
    } else {
        tableProposals.find('th').eq(2).hide();
        tableLabels.find('th').eq(3).hide();
        $("*[id$='_positionForce']").each(function () {
           $(this).parent('td').hide();
       });
    }
}

// Set the proposals order
function setOrderProposal() {

    var order = 1;

    $('#newTableProposal').find('tr:not(:first)').each(function () {
        $(this).find('input:first').val(order);
        order++;
    });
}

// Set the labels order
function setOrderLabel() {

    var order = 1;

    $('#newTableLabel').find('tr:not(:first)').each(function () {
        $(this).find('input:first').val(order);
        order++;
    });
}
