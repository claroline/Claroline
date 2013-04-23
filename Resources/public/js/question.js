function cherche_type_ajax(exoID, displayFormType) {
    //"use strict";

    var indice_type = $("#menu_type_question option:selected").index();

    $.ajax({
        type: "POST",
        url: displayFormType,
        data: { indice_type: indice_type, exercise: exoID },
        cache: false,
        success: function (data) {
            $("#formulaire_interactions").html(data);
        }
    });
}

function cherche_type(warningChangeTypeQ, exoID, displayFormType) {
    //"use strict";

    if (($("#formulaire_interactions").find("div").length) > 0) {
        if (confirm(warningChangeTypeQ)) {
            cherche_type_ajax(exoID, displayFormType);
        }
    } else {
        cherche_type_ajax(exoID, displayFormType);
    }
}