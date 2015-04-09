var typeOpen;
var container;
var tablewr;

function insertStyle(tOpen) {

    typeOpen = JSON.parse(tOpen);

    $('#ujm_exobundle_interactionopentype_interaction').find('div').first().find('label').first().remove();

     $('#ujm_exobundle_interactionopentype_typeopenquestion').children('option').each(function() {
         if (typeOpen[$(this).val()] == 2) {
             $(this).prop('selected', true);
         } else {
             //$(this).attr('disabled', 'disabled');
         }
     });
}

function CheckForm() {
    /*if ($("*[id$='_penalty']").length > 0) {
        $("*[id$='_penalty']").val($("*[id$='_penalty']").val().replace(/[-]/, ''));
    }*/
}

function formWordResponse() {

    container = $('div#ujm_exobundle_interactionopentype_wordResponses');
    tablewr = $('#tablewr');
    $('.form-collection-add').remove();

    $('#add_wr').click(function (e) {
        $('#tablewr').find('tbody').append('<tr></tr>');
        deleteWr = 'Meuh !'
        addWr(container, deleteWr);
        e.preventDefault(); // prevent add # in the url
        return false;
    });
}

// Add a choice
function addWr(container, deleteWr) {
    var uniqChoiceID = false;

    var index = $('#tablewr').find('tr:not(:first)').length;

    while (uniqChoiceID == false) {
        if ($('#ujm_exobundle_interactionopentype_wordResponses_' + index + '_label').length) {
            index++;
        } else {
            uniqChoiceID = true;
        }
    }

    // change the "name" by the index and delete the symfony delete form button
    var contain = $(container.attr('data-prototype').replace(/__name__label__/g, 'wr n°' + (index))
        .replace(/__name__/g, index)
        .replace('<a class="btn btn-danger remove" href="#">Delete</a>', '')
    );

    // Add the button to delete a choice
    addDeleteMeuh(contain, deleteWr);

    // Add the modified dataprototype to the page
    container.append(contain);

    container.find('.row').each(function () {
        addRowToTablewr($(this));
    });

    // Add the delete button
    $('#tablewr').find('tr:last').append('<td class="classic"></td>');
    $('#tablewr').find('td:last').append(contain.find('a.btn-danger'));

    // Remove the useless fileds form
    container.remove();
}

// A supprimer avec refonte des interface, cette fonction dupliquée dans plusieurs JS sera dans allQuestionsType
function addDeleteMeuh(tr, deleteTrans) {
    alert('Ceci est un message de la fonction "addDeleteMeuh" : Ne pas oubliez de me supprimer avec le merge des vues !!!!!!!!!');
    // Create the button to delete
    var delLink = $('<a title="'+deleteTrans+'" href="#" class="btn btn-danger"><i class="fa fa-close"></i></a>');
    // Add the button to the row
    tr.append(delLink);
    // When click, delete the matching row in the table
    delLink.click(function(e) {
    $(this).parent('td').parent('tr').remove();
        e.preventDefault();
        return false;
    });
    alert('PS : n\'oubliez pas de renommer mon appel et virer les alerts ...');
}

function addRowToTablewr(row) {
    if (row.find('input').length) {
        if (row.find('input').attr('id').indexOf('ordre') == -1) {
            $('#tablewr').find('tr:last').append('<td class="classic"></td>');
            $('#tablewr').find('td:last').append(row.find('input'));
        }
    }
}

$('#ujm_exobundle_interactionopentype_typeopenquestion').change( function () {
    if (typeOpen[$(this).val()] == 4) {
         $('#qOpenOneWord').css('display', 'block');
         $('#qOpenScoreMaxLongResp').css('display', 'none');
         $('#ujm_exobundle_interactionopentype_scoreMaxLongResp').val(0);
         formWordResponse();
     } else if (typeOpen[$(this).val()] == 2) {
         $('#qOpenScoreMaxLongResp').css('display', 'block');
         $('#qOpenOneWord').css('display', 'none');
         $('#ujm_exobundle_interactionopentype_scoreMaxLongResp').val('');
     }
});