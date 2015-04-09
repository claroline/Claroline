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
    
    container = $('#ujm_exobundle_interactionopentype_wordResponses');
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

    // Remove the useless fileds form
    container.remove();
}

$('#ujm_exobundle_interactionopentype_typeopenquestion').change( function () {
    if (typeOpen[$(this).val()] == 4) {
         $('#qOpenOneWord').css('display', 'block');
         $('#qOpenScoreMaxLongResp').css('display', 'none');
         formWordResponse();
     } else if (typeOpen[$(this).val()] == 2) {
         $('#qOpenScoreMaxLongResp').css('display', 'block');
         $('#qOpenOneWord').css('display', 'none');
     }
});