var typeOpen;
var container;
var tablewr;
var deleteWr;
var checkModal = false; //Checks whether the modal (question for a word) is show or hidden
var editionAd;
var commentRep;
var keyWordTrans;
var keysWords;
var response;
var numericalResponse;

function insertStyle(tOpen, deleteTrans, edition, comment, keyWordTrans, responseTrans, numericalResponseTrans, keysWordsTrans) {

    typeOpen = JSON.parse(tOpen);
    container = $('div#ujm_exobundle_interactionopentype_wordResponses');
    tablewr = $('#tablewr');
    deleteWr = deleteTrans;
    editionAd = edition;
    commentRep = comment;
    keyWord = keyWordTrans;
    response = responseTrans;
    numericalResponse = numericalResponseTrans;
    keysWords = keysWordsTrans;

    $('#ujm_exobundle_interactionopentype_interaction').find('div').first().find('label').first().remove();
    $('.form-collection-add').remove();

    $('#add_wr').click(function (e) {
        $('#tablewr').find('tbody').append('<tr></tr>');
        addWr(container, deleteWr);
        e.preventDefault(); // prevent add # in the url
        return false;
    });

    $("#ujm_exobundle_interactionopentype_typeopenquestion option[value='2']").prop('selected', true);
}

function formWordResponseEdit(nbResponses) {
    // Get the form field to fill rows of the choices' table
    container.children().first().children('div').each(function () {
        // Add a row to the table
        $('#tablewr').find('tbody').append('<tr></tr>');
        var index = $('#tablewr').find('tr:not(:first)').length-1;
        $(this).find('.row').each(function () {
            addRowToTablewr($(this),index);
              //Displays enabled tinyMCE
            textareaAdvancedEdition();
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
        if ($('#ujm_exobundle_interactionopentype_wordResponses_' + index + '_response').length) {
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
        addRowToTablewr($(this),index);
    });

    // Add the delete button
    $('#tablewr').find('tr:last').append('<td class="classic"></td>');
    $('#tablewr').find('td:last').append(contain.find('a.btn-danger'));

    // Remove the useless fileds form
    container.remove();
}

function addRowToTablewr(row,index) {
    if (row.find('input').length) {
        if (row.find('input').attr('id').indexOf('ordre') == -1) {
            $('#tablewr').find('tr:last').append('<td class="classic"></td>');
            $('#tablewr').find('td:last').append(row.find('input'));
        }
         //Add the field of type textarea feedback

    }
    if (row.find('*[id$="_feedback"]').length) {
            var idFeedbackVal = row.find('textarea').attr("id");
            //Adds a cell array with a comment button
            $('#tablewr').find('tr:last').append('<td class="classic"><a class="btn btn-default" id="btn_' + idFeedbackVal + '" title="'+commentRep+'" onClick="addTextareaFeedback(\'span_' + idFeedbackVal + '\',\'btn_' + idFeedbackVal + '\')" ><i class="fa fa-comments-o"></i></a><span id="span_' + idFeedbackVal + '" class="input-group" style="display:none;"></span></td>');
            //Adds the textarea and its advanced edition button (hidden by default)
            $('#span_' + idFeedbackVal).append(row.find('*[id$="_feedback"]'));
            $('#span_' + idFeedbackVal).append('<span class="input-group-btn"><a class="btn btn-default" id="btnEdition_' + idFeedbackVal + '" onClick="advancedEdition(\'ujm_exobundle_interactionopentype_wordResponses_' + index + '_feedback\',\'btnEdition_' + idFeedbackVal + '\',event);" title="' + editionAd + '"><i class="fa fa-font"></i></a></span>');
        }
}

function openEdit(nbResponses) {
    codeOpen = typeOpen[$('#ujm_exobundle_interactionopentype_typeopenquestion').val()];
    if (codeOpen == 4) {
        showOpenWord();
    } else if (codeOpen == 3) {
        showShortResponse();
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
    $('#add_wr').text(keyWord);
    $('#tablewr').find('th:first').text(keysWords);
    $('#tablewr').find('th').eq(2).css({'display':'block'});
    $('#tablewr').find('td').eq(2).each(function () {
            $(this).css({'display':'block'});
    });
}

function showOpenNumerical(nbResponses) {
    showOpenWord(nbResponses);
    $('#add_wr').text(response);
    $('#tablewr').find('th:first').text(numericalResponse);
    $('#tablewr').find('th').eq(2).css({'display':'none'});
    $('#tablewr').find('td').eq(2).each(function () {
            $(this).css({'display':'none'});
    });
}

function showShortResponse() {
    showOpenWord();
}

function showLongResponse() {
    $('#qOpenScoreMaxLongResp').css('display', 'block');
    $('#qOpenOneWord').css('display', 'none');
    $('#ujm_exobundle_interactionopentype_scoreMaxLongResp').val('');
    var lengthTab = $("#tablewr tr").length;
    //removes empty lines
    for (var i = 1; i < lengthTab; i++) {
        if ($("#ujm_exobundle_interactionopentype_wordResponses_" + i + "_response").val() === '' || $("#ujm_exobundle_interactionopentype_wordResponses_" + i + "_score").val() === '')
        {
            $("#ujm_exobundle_interactionopentype_wordResponses_" + i + "_response").parent('td').parent('tr').remove();
        }
    }
}

$('#ujm_exobundle_interactionopentype_typeopenquestion').change(function () {
    if (typeOpen[$(this).val()] == 4) {
        showOpenWord(0);
    } else if (typeOpen[$(this).val()] == 2) {
        showLongResponse();
    } else if (typeOpen[$(this).val()] == 3) {
        showOpenWord(0);
    } else if (typeOpen[$(this).val()] == 1) {
        showOpenNumerical(0);
    }
});

/**
 * check if there is space in "typeOneWord"
 *
 */
function checkFormOpen() {
    if (checkModal === false) {
        var check = false;
        if (typeOpen[$('#ujm_exobundle_interactionopentype_typeopenquestion').val()] === 4) {
            $("*[id$='_response']").each(function () {
                if ($(this).val().match(/\s/))
                {
                    check = true;
                }
            });
        }
        if (check === true)
        {
            $('#confirm-modal').modal('show');
            return false;
        }
        else {
            return true;
        }
    } else {
        $('#confirm-modal').modal('hide');
        return true;
    }
}