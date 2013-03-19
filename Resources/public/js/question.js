function cherche_type(Warning_change_type_q, exoID, afficher_form_type )
{
    if (($('#formulaire_interactions').find('div').length) > 0) 
    {
        if(confirm(Warning_change_type_q))
        {
            cherche_type_ajax (exoID, afficher_form_type);
        }
    }
    else
    {
        cherche_type_ajax (exoID, afficher_form_type);
    }
}

function cherche_type_ajax (exoID, afficher_form_type)
{
    var indice_type = $("#menu_type_question option:selected").index();

    $.ajax({
        type: "POST",
        url: afficher_form_type,
        data: { indice_type: indice_type, exercise: exoID },
        cache: false,
        success: function(data){
        $('#formulaire_interactions').html(data);
        }
    });    
}