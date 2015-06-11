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

    // InnovaCollecticiel
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

    // InnovaCollecticiel
    // Ajout pour le traitement du clic sur le bouton "Oui, valider"
    $('#modal_confirm').on('click', function(event) {
        var selector = "#document_id_"+$(this).attr("data-document_id"); // Extract info from data-* attributes
        var row = "row_"+$(this).attr("data-document_id"); // Extract info from data-* attributes
        var documentId = $(this).attr("data-document_id");
        console.log(documentId);
        var button = document.getElementById("delete_" + documentId);
        console.log(button);
        
        $(button).hide();

        $(selector).prop('checked', true); // Cocher la case "Valider"
        $(selector).prop('disabled', true); // Ne pas pouvoir modifier cette ligne

        // Fermeture de la modal
        $('#validate-modal').modal('hide');
    });

});
