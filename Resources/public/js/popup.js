$(document).ready(function () {
    'use strict';

    var modalNewForm = null;

    $('a.launch-modal').on('click', function (event) {
        event.preventDefault();
        var currentPath = $(this).attr('href');
        $.get(currentPath)
            .always(function () {
                if (modalNewForm !== null) {
                    modalNewForm.remove();
                }
            })
            .done(function (data) {
                $('body').append(data);
                modalNewForm = $('#modal-content');
                modalNewForm.modal('show');

                modalNewForm.on('hidden.bs.modal', function () {
                    modalNewForm.remove();
                });
            })
        ;
    });

    // InnovaERV
    // Ajout pour le traitement de la case à cocher lors de la soumission de documents
    $('#validate-modal').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget); // Button that triggered the modal
      var documentId = button.data('document_id'); // Extract info from data-* attributes
      // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
      // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
      var modal = $(this);
      modal.find('#modal_confirm').attr("data-document_id", documentId); //TODO change this to use data() instead of attr()
      //documentId
      //
    })

    // InnovaERV
    // Ajout pour le traitement du clic sur le bouton "Oui, valider"
    $('#modal_confirm').on('click', function(event) {
        var selector = "#document_id_"+$(this).attr("data-document_id"); // Extract info from data-* attributes
        var row = "row_"+$(this).attr("data-document_id"); // Extract info from data-* attributes
        var documentId = $(this).attr("data-document_id");
        var button = document.getElementById("delete_" + documentId);
        
        $(button).hide();

        $(selector).prop('checked', true); // Cocher la case "Valider"
        $(selector).prop('disabled', true); // Ne pas pouvoir modifier cette ligne

        // Récupération de l'id du document
        var docId = $(this).attr("data-document_id");

        // Ajax : appel de la route qui va mettre à jour la base de données
        // Ajax : route "innova_collecticiel_validate_document" dans DocumentController
        var req = "#request_id_"+$(this).attr("data-document_id"); // Extract info from data-* attributes
        alert(req);

        $.ajax({
            url: Routing.generate('innova_collecticiel_validate_document',
                { documentId: docId
                }),
            method: "POST",
            data:
            {
                documentId: docId
            },
            done : function(data) {
                console.log("done");
                $(req).show();
            }
        });

                console.log("modal");
                $(req).show();
        // Fermeture de la modal
        $('#validate-modal').modal('hide');

    });

    // InnovaERV
    // Ajout pour le traitement de la demande de commentaire : mise à jour de la table Document
    // Mise à jour de la colonne "validate"
    $('.document_validate').on('click', function(event) {
    });

});
