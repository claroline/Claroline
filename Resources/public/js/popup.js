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
    });

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

        // Ajout : vu avec Arnaud.
        // Ajout de "complete" afin de mettre à jour la partie "HTML" qui va actualisé et afficher "Demande transmise"
        $.ajax({
            url: Routing.generate('innova_collecticiel_validate_document',
                { documentId: docId
                }),
            method: "POST",
            data:
            {
                documentId: docId
            },
            complete : function(data) {
                $("#is-validate-"+docId).html(data.responseText);
            }
        });

        // Fermeture de la modal
        $('#validate-modal').modal('hide');

    });


    // InnovaERV
    // Ajout pour le traitement de la modal de choix du type d'accusé de réception
    $('#modal_confirm_return_receipt').on('click', function(event) {
        event.preventDefault();
 
        var returnReceiptId;
        if (document.getElementById('choix1').checked) {
            returnReceiptId = document.getElementById('choix1').value;
        }
        if (document.getElementById('choix2').checked) {
            returnReceiptId = document.getElementById('choix2').value;
        }
        if (document.getElementById('choix3').checked) {
            returnReceiptId = document.getElementById('choix3').value;
        }
        if (document.getElementById('choix4').checked) {
            returnReceiptId = document.getElementById('choix4').value;
        }
        if (document.getElementById('choix5').checked) {
            returnReceiptId = document.getElementById('choix5').value;
        }

//for(var i = 0; i < boutons.length; i++){
//if(boutons[i].checked){
//valeur = boutons[i].value;
//}
//}

//        alert("Choix de AR ");alert(returnReceiptId);

         // Récupération de l'id du document
        var dropzoneId = $(this).attr("data-dropzone_id");
//        alert("Dropzone ");alert(dropzoneId);


//        var docs = $('#checkbox').is(':checked')

//        $("input.toto :checked")


        var arrayDocsId = [];

        $("input[type='checkbox']:checked").each(
            function() {
                arrayDocsId.push($(this).attr('id'));
            });          

        alert("arrayDocsId ");alert(arrayDocsId);


        $.ajax({
            url: Routing.generate('innova_collecticiel_return_receipt',
                { 
                dropzoneId: dropzoneId,
                returnReceiptId: returnReceiptId,
                }),
            method: "GET",
            data:
            {
                arrayDocsId: arrayDocsId
            },
            complete : function(data) {
            }
        });

        // Fermeture de la modal
        $('#validate-modal').modal('hide');

    });


    // InnovaERV
    // Ajout pour le traitement de la demande de commentaire : mise à jour de la table Document
    // Mise à jour de la colonne "validate"
    $('.document_validate').on('click', function(event) {
    });
    
    $('a.cancel_button').on('click', function(event) {
        event.preventDefault();
        var docId = $(this).attr("data-document_id");

        $.ajax(
            {
                url: Routing.generate('innova_collecticiel_unvalidate_document', {documentId: docId}),
                method: "POST",
                complete: function(data) {
                    $("#is-validate-" + docId).html(data.responseText);
                }
            }
        );
    });

    // InnovaERV : ajout du bouton "Retour" dans la liste des commentaires.
    $('.backLink').on('click', function(event) {
        event.preventDefault();
        history.back(-1);
    });

});
