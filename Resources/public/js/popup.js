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

    // Ajout pour le traitement du clic sur le bouton "Oui, valider"
    $('#modal_confirm').on('click', function(event) {

        var selector = "#document_id_"+$(this).attr("data-document_id"); // Extract info from data-* attributes
        var row = "row_"+$(this).attr("data-document_id"); // Extract info from data-* attributes

        $(selector).prop('checked', true);
        $(selector).prop('disabled', true);

        //$('#row input[name=selector]').val(['0']);
        // var cases = $("#row_"+selector).find(':checkbox');
        // cases.attr('checked', true);

        // $("#row_"+attr("data-document_id").find('checkbox').prop('checked, true');
        // $('input[id=document_id_11]').prop('checked');
        // $("#row_"+attr("data-document_id")).find("#document_id_"+data-document_id).prop("checked", "checked");
        // $("#document_id_"+data-document_id).prop("checked", "checked");
        // $("#row_"+attr("data-document_id")).find(':checkbox').attr('checked', true);

        //$('input[name=selector]').prop('checked');
        // Fermeture de la modal
        $('#validate-modal').modal('hide');
    });


});