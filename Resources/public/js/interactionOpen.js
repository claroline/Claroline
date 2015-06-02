var typeOpen;
var container;
var tablewr;
var deleteWr;

function insertStyle(tOpen, deleteTrans) {

    typeOpen = JSON.parse(tOpen);
    container = $('div#ujm_exobundle_interactionopentype_wordResponses');
    tablewr = $('#tablewr');
    deleteWr = deleteTrans;

    $('#ujm_exobundle_interactionopentype_interaction').find('div').first().find('label').first().remove();
    $('.form-collection-add').remove();

    $('#add_wr').click(function (e) {
        $('#tablewr').find('tbody').append('<tr></tr>');
        addWr(container, deleteWr);
        e.preventDefault(); // prevent add # in the url
        return false;
    });

    //todo delete to implement numerical questions
    $("#ujm_exobundle_interactionopentype_typeopenquestion option[value='1']").remove();
}

function formWordResponseEdit(nbResponses) {
    // Get the form field to fill rows of the choices' table
    container.children().first().children('div').each(function () {
        // Add a row to the table
        $('#tablewr').find('tbody').append('<tr></tr>');

        $(this).find('.row').each(function () {
            addRowToTablewr($(this));
        });
        if (nbResponses == 0) {
            // Add the delete button
            $('#tablewr').find('tr:last').append('<td class="classic"></td>');
            addDelete($('#tablewr').find('td:last'), deleteWr);
        }

    });
    container.remove();
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
    addDelete(contain, deleteWr);

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

function addRowToTablewr(row) {
    if (row.find('input').length) {
        if (row.find('input').attr('id').indexOf('ordre') == -1) {
            $('#tablewr').find('tr:last').append('<td class="classic"></td>');
            $('#tablewr').find('td:last').append(row.find('input'));
        }
    }
}

function openEdit(nbResponses) {
    codeOpen = typeOpen[$('#ujm_exobundle_interactionopentype_typeopenquestion').val()];
    if (codeOpen == 4) {
        showOpenWord();
    } else if (codeOpen == 3) {
        showShortResponse ();
    }
    formWordResponseEdit(nbResponses);
}

function showOpenWord(nbResponses) {
    $('#qOpenOneWord').css('display', 'block');
    $('#qOpenScoreMaxLongResp').css('display', 'none');
    $('#ujm_exobundle_interactionopentype_scoreMaxLongResp').val(0);
  //Check if not edition or not form error
    if (nbResponses === 0) {
        if ($('#tablewr tr').length === 1) {
            $('#tablewr').find('tbody').append('<tr></tr>');
            addWr(container, deleteWr);
        }
    }
}

function showShortResponse () {
    showOpenWord ();
}

function showLongResponse() {
    $('#qOpenScoreMaxLongResp').css('display', 'block');
    $('#qOpenOneWord').css('display', 'none');
    $('#ujm_exobundle_interactionopentype_scoreMaxLongResp').val('');
    var lengthTab = $("#tablewr tr").length;
    //removes empty lines
    for(var i=1; i<lengthTab;i++) {  
        if ($("#ujm_exobundle_interactionopentype_wordResponses_"+i+"_response").val() === '' || $("#ujm_exobundle_interactionopentype_wordResponses_"+i+"_score").val()=== '')
        {
            $("#ujm_exobundle_interactionopentype_wordResponses_"+i+"_response").parent('td').parent('tr').remove();
        }
    }
}

$('#ujm_exobundle_interactionopentype_typeopenquestion').change( function () {
    if (typeOpen[$(this).val()] == 4) {    
        showOpenWord(0);
    } else if (typeOpen[$(this).val()] == 2) {
        showLongResponse();
    } else if (typeOpen[$(this).val()] == 3) {
        showOpenWord (0);
    }
});