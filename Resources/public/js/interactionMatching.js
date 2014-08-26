var containerLabel = $('div#ujm_exobundle_interactionmatchingtype_labels'); // Div which contain the dataprototype of labels
var containerProposal = $('div#ujm_exobundle_interactionmatchingtype_proposals');

var tableLabels = $('#tableLabel'); // div which contain the choices array
var tableProposals = $('#tableProposal');

var typeMatching;

// Question creation
function creationMatching(addchoice, addProposal, deletechoice, LabelValue, ScoreRight, ProposalValue, tMatching){

    //initialisation of variables
    var indexLabel; // number of choices
    var indexProposal;
    var codeContainerLabel = 0; // to differentiate containers
    var codeContainerProposal = 1;
    
    typeMatching = JSON.parse(tMatching);
    
    tableCreation(containerProposal, tableProposals, addProposal, deletechoice, LabelValue, ScoreRight, ProposalValue, 0, codeContainerProposal);    
    tableCreation(containerLabel, tableLabels, addchoice, deletechoice, LabelValue, ScoreRight, ProposalValue, 0, codeContainerLabel);
    
    // Number of choice initially
    indexLabel = containerLabel.find(':input').length;    
    indexProposal = containerProposal.find(':input').length;
    
    // If no choice exist, add two choices by default in the container Label
    if (indexLabel == 0) {
        addChoice(containerLabel, deletechoice, tableLabels, codeContainerLabel);
        $('#newTableLabel').find('tbody').append('<tr></tr>');
        addChoice(containerLabel, deletechoice, tableLabels, codeContainerLabel);
    // If choice already exist, add button to delete it
    } else {
        tableLabels.children('tr').each(function() {
            adddelete($(this), deletechoice, codeContainerLabel);
        });
    }
    // If no choice exist, add two choices by default in the container Proposal
    if (indexProposal == 0) {
        addChoice(containerProposal, deletechoice, tableProposals, codeContainerProposal);
        $('#newTableProposal').find('tbody').append('<tr></tr>');
        addChoice(containerProposal, deletechoice, tableProposals, codeContainerProposal);
        // If choice already exist, add button to delete it
    } else {
        tableProposals.children('tr').each(function() {
        adddelete($(this), deletechoice, codeContainerProposal);
        });
    }
}

// Question edition
function creationMatchingEdit(){
}

function addChoice(container, deletechoice, table, codeContainer){
    var contain;
    var uniqChoiceId = false;
    var indexProposal = $('#newTableProposal').find('tr:not(:first)').length;
    var indexLabel = $('#newTableLabel').find('tr:not(:first)').length;
    while (uniqChoiceId == false) {
        if (codeContainer == 0){
            if ($('#ujm_exobundle_interactionmatchingtype_labels_' + indexLabel + '_value').lenght){
                indexLabel++;
            } else{
                uniqChoiceId = true;
            }
            // Change the "name" by the index and delete the symfony delete form button
            contain = $(container.attr('data-prototype').replace(/__name__label__/g, 'Choice n°' + (indexLabel))
                .replace(/__name__/g, indexLabel)
                .replace('<a class="btn btn-danger remove" href="#">Delete</a>', '')
            );
        } else {
            if ($('#ujm_exobundle_interactionmatchingtype_proposals_' + indexProposal + '_value').lenght){
                indexProposal++;
            } else{
                uniqChoiceId = true;
            }
            // Change the "name" by the index and delete the symfony delete form button
            contain = $(container.attr('data-prototype').replace(/__name__label__/g, 'Choice n°' + (indexProposal))
                .replace(/__name__/g, indexProposal)
                .replace('<a class="btn btn-danger remove" href="#">Delete</a>', '')
            );
            
        }
    }

    // Add the button to delete a choice
    adddelete(contain, deletechoice, codeContainer);
    container.append(contain);
    // Get the form field to fill rows of the choices' table
    container.find('.row').each(function () {
        if (codeContainer == 0){
            fillChoicesArrayLabel();
        }else{
            fillChoicesArrayProposal();
        }
        
    });
    
    // Add the delete button each row of table
    if (codeContainer ==0)
    {
        $('#newTableLabel').find('tr:last').append('<td class="classic"></td>');
        $('#newTableLabel').find('td:last').append(contain.find('a:contains("' + deletechoice + '")'));
    } else {
        $('#newTableProposal').find('tr:last').append('<td class="classic"></td>');
        $('#newTableProposal').find('td:last').append(contain.find('a:contains("' + deletechoice + '")'));
    }
    
    // Remove the useless fileds form
    container.remove();
    table.next().remove();
}

//check if the form is valid
function check_form(){
//    if (($('#newTableLabel').find('tr:not(:first)').length) < 2) {
//            alert(nbrChoices);
//            return false;
//    }
//    if (($('#newTableProposal').find('tr:not(:first)').length) < 2) {
//            alert(nbrChoices);
//            return false;
//    }
}

function fillChoicesArrayLabel() {

    // Add the field of type textarea
    if (containerLabel.find('.row').find('textarea').length) {
        $('#newTableLabel').find('tr:last').append('<td class="classic"></td>');
        $('#newTableLabel').find('td:last').append(containerLabel.find('.row').find('textarea'));
    }
    
//     Add the field of type input
    if (containerLabel.find('.row').find('input').length) {
        $('#newTableLabel').find('tr:last').append('<td class="classic"></td>');
        $('#newTableLabel').find('td:last').append(containerLabel.find('.row').find('input'));
    }
}

function fillChoicesArrayProposal() {

    // Add the field of type textarea
    if (containerProposal.find('.row').find('textarea').length) {
        $('#newTableProposal').find('tr:last').append('<td class="classic"></td>');
        $('#newTableProposal').find('td:last').append(containerProposal.find('.row').find('textarea'));
    }
    
//     Add the field of type input
    if (containerProposal.find('.row').find('input').length) {
        $('#newTableProposal').find('tr:last').append('<td class="classic"></td>');
        $('#newTableProposal').find('td:last').append(containerProposal.find('.row').find('input'));
    }
}

function adddelete(tr, deletechoice, codeContainer){
    var delLink;
    // Create the button to delete a choice
    if(codeContainer == 0){
        delLink = $('<a href="newTableLabel" class="btn btn-danger">' + deletechoice + '</a>');
    } else {
        delLink = $('<a href="newTableProposal" class="btn btn-danger">' + deletechoice + '</a>');
    }
    
    // Add the button to the row
    tr.append(delLink);
    
    
    // When click, delete the matching choice's row in the table
    delLink.click(function(e) {
        $(this).parent('td').parent('tr').remove();
        e.preventDefault();
        return false;
    });
}

function tableCreation(container, table, button, deletechoice, LabelValue, ScoreRight, ProposalValue, nbResponses, codeContainer){
    if (nbResponses == 0) {
        // Creation of the table
        if (codeContainer == 0){
            table.append('<table id="newTableLabel" class="table table-striped table-bordered table-condensed"><thead><tr style="background-color: lightsteelblue;"><th class="classic">'+LabelValue+'</th><th class="classic">'+ScoreRight+'</th><th class="classic">'+deletechoice+' le choix</th></tr></thead><tbody><tr></tr></tbody></table>');
        } else {
            table.append('<table id="newTableProposal" class="table table-striped table-bordered table-condensed"><thead><tr style="background-color: lightsteelblue;"><th class="classic">'+ProposalValue+'</th><th class="classic">'+ProposalValue+'</th><th class="classic">'+deletechoice+' la proposition</th></tr></thead><tbody><tr></tr></tbody></table>');
        }

        // Creation of the button add
        var add = $('<a href="#" id="add_choice" class="btn btn-primary"><i class="fa fa-plus"></i>&nbsp;'+button+'</a>');
        
        // Add the button add
        table.append(add);
        add.click(function (e) {
            if(codeContainer == 0){
                $('#newTableLabel').find('tbody').append('<tr></tr>');
            } else{
                $('#newTableProposal').find('tbody').append('<tr></tr>');
            }
            addChoice(container, deletechoice, table, codeContainer);
            e.preventDefault(); // prevent add # in the url
            return false;
        });
    } else {
        // Add the structure of the table
        if (codeContainer == 0){
            table.append('<table id="newTableLabel" class="table table-striped table-bordered table-condensed"><thead><tr style="background-color: lightsteelblue;"><th class="classic">'+LabelValue+'</th><th class="classic">'+ScoreRight+'</th><th class="classic">'+deletechoice+' le choix</th></tr></thead><tbody><tr></tr></tbody></table>');
        } else {
            table.append('<table id="newTableProposal" class="table table-striped table-bordered table-condensed"><thead><tr style="background-color: lightsteelblue;"><th class="classic">'+ProposalValue+'</th><th class="classic">'+deletechoice+' la proposition</th></tr></thead><tbody><tr></tr></tbody></table>');
        }
    }
}