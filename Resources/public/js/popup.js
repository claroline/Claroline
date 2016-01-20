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
    // Ajout pour le traitement de la case Ã  cocher pour la crÃ©ation de commentaire Ã  la volÃ©e
    $('.comment_validate').on('click', function (event) {
        event.preventDefault();
 
        // RÃ©cupÃ©ration de l'id du document
        var dropzoneId = $(this).attr("data-dropzone_id");

        var arrayDocsId = [];
        var arrayDropsId = [];

        $("input[type='checkbox']:checked").each(
            function() {
                if ($(this).attr('id') !== "document_id_0") {
                    arrayDocsId.push($(this).attr('id'));
                    arrayDropsId.push($(this).attr("data-drop_id"));
                }
            });          

        $.ajax({
            url: Routing.generate('innova_collecticiel_add_more_comments',
                { 
                    dropzoneId: dropzoneId,
                }),
            method: "GET",
            data:
            {
                arrayDocsId: arrayDocsId,
                arrayDropsId: arrayDropsId
            },
            complete : function(data) {
                var data_link = $.parseJSON(data.responseText)
//                    var resource = data[0];

                if (data_link !== 'false') {
                    document.location.href=data_link.link;
                }
            }
        });

    });

    // InnovaERV
    // Ajout pour le traitement de la case Ã  cocher lors de la soumission de documents
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

        // RÃ©cupÃ©ration de l'id du document
        var docId = $(this).attr("data-document_id");

        // Ajax : appel de la route qui va mettre Ã  jour la base de donnÃ©es
        // Ajax : route "innova_collecticiel_validate_document" dans DocumentController
        var req = "#request_id_"+$(this).attr("data-document_id"); // Extract info from data-* attributes

        // Ajout : vu avec Arnaud.
        // Ajout de "complete" afin de mettre Ã  jour la partie "HTML" qui va actualiser et afficher "Demande transmise"
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
    // Ajout pour le traitement de la case Ã  cocher lors de la soumission de documents
    $('#validate-modal-return-receipt').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget); // Button that triggered the modal
      var documentId = button.data('document_id'); // Extract info from data-* attributes
      // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
      // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
      var modal = $(this);
      $(".data-document_id").append(documentId);
      modal.find('#modal_confirm_return_receipt').attr("data-document_id", documentId);
    });

    // InnovaERV
    // Ajout pour le traitement de la modal de choix du type d'accusÃ© de rÃ©ception
    $('#modal_confirm_return_receipt').on('click', function(event) {
        event.preventDefault();
 
        var returnReceiptId;
        if (document.getElementById('choix0').checked) {
            returnReceiptId = document.getElementById('choix0').value;
        }
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

        // RÃ©cupÃ©ration de l'id du document
        var dropzoneId = $(this).attr("data-dropzone_id");

        // RÃ©cupÃ©ration de l'id du document
        var documentId = $(this).attr("data-document_id");

        var arrayDocsId = [];

        if (!documentId)
        {
            $("input[type='checkbox']:checked").each(
                function() {
                    arrayDocsId.push($(this).attr('id'));
            });          
        }
        else
        {
            var numDocPush = $(this).attr('data-document_id');
            var docPush = "document_id_"+$(this).attr('data-document_id');
            arrayDocsId.push(docPush);
        }

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
                var data_link = $.parseJSON(data.responseText)

                if (data_link !== 'false') {
                    document.location.href=data_link.link;
                }

            }
        });

        // Fermeture de la modal
        $('#validate-modal-return-receipt').modal('hide');

    });

    // InnovaERV
    // Ajout pour le traitement de la demande de commentaire : mise Ã  jour de la table Document
    // Mise Ã  jour de la colonne "validate"
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

    // InnovaERV : sÃ©lection et dÃ©selection dans la liste des demandes adressÃ©es.
    $('#document_id_0').on('click', function(event) {
        if($(this).is(':checked')){
            $('input[type=checkbox]').each(function(i,k){
                $(k).prop('checked',true);
              })
        }
        else
        {
            $('input[type=checkbox]').each(function(i,k){
                $(k).prop('checked',false);
            })
        }
    })

    $('input[type=checkbox]').not('#document_id_0').click(function() {
        $('#document_id_0').prop('indeterminate', true);
    })

    // InnovaERV : ajout du bouton "Retour" dans la liste des commentaires.
    $('.backLink').on('click', function(event) {
        event.preventDefault();
        history.back(-1);
    });

});
