var typeOpen;

function insertStyle(tOpen) {

    typeOpen = JSON.parse(tOpen);

    $('#ujm_exobundle_interactionopentype_interaction').find('div').first().find('label').first().remove();
    
     $('#ujm_exobundle_interactionopentype_typeopenquestion').children('option').each(function() {
         if (typeOpen[$(this).val()] == 2) {
             $(this).prop('selected', true);
         } else {
             $(this).attr('disabled', 'disabled');
         }
     });
}

function CheckForm() {
    /*if ($("*[id$='_penalty']").length > 0) {
        $("*[id$='_penalty']").val($("*[id$='_penalty']").val().replace(/[-]/, ''));
    }*/
}