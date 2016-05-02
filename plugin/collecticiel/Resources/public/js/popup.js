$('#add-criterion-button-innova2').on('click', function(event) {

        event.preventDefault();

        $('.disabled-during-edition').attr('disabled', 'disabled');
        tinyMCE.get('innova_collecticiel_criteria_form_correctionInstruction').getBody().setAttribute('contenteditable', false);
        //$('.icap_dropzone_criteria_form_correctionInstruction').attr('disabled','disabled');
        $('.criteria-form-button').attr('disabled', 'disabled');

        var criterionId = $(this).data('criterion');
        var $form = $('#global_form');
        $('#addCriteriaReRouting').val('add-criterion');
        $.ajax({
            url: $form.attr('action'),
            type: $form.attr('method'),
            data: $form.serialize(),
            success: function (data) {
                console.log(data);
                $.get($('.add-criterion-button').attr('href'))
                    .done(function (data) {
                        resetTiny();
                        $('.new-criteria-zone').empty();
                        $('.criterion-row > .criterion-edit').empty();

                        $('.template > .template-criteria-zone').clone().appendTo('.new-criteria-zone');
                        $('.new-criteria-zone .new-criteria-form').append(data);

                        $('.column-input input').val(totalColumn);
                        $('.comment-input input').val(comment);

                        $('.add-criteria-zone').hide();
                        $('.new-criteria').show();
                        $('.new-criteria-zone > .template-criteria-zone').show();

                        $('.new-criteria-zone > .template-criteria-zone .form-buttons').hide();

                        $('.add-remove-column').show();
                        initTinyMCE(stfalcon_tinymce_config);

                        var top = $('#new-criteria').offset().top;
                        top = top - 50;
                        $('body,html').scrollTop(top);
                        setSaveListener();
                    })
                ;

            }
        });

});


$(document).ready(function () {

    'use strict';

    // BEGIN ADD LINK
    // setup an "add a tag" link
    var $addTagLink = $('<a href="#" class="add_tag_link">Degré d\'appréciation</a>');
    var $newLinkLi = $('<li></li>').append($addTagLink);

    // Get the ul that holds the collection of tags
    var $collectionHolder = $('ul.tags');
    
    // add the "add a tag" anchor and li to the tags ul
    $collectionHolder.append($newLinkLi);
    
    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    $collectionHolder.data('index', $collectionHolder.find(':input').length);

    // Je divise par 2 car pour UNE donnée en base, j'envoie également UNE donnée en hidden
    var $nb = ($collectionHolder.find(':input').length)/2;

    // we must have at least 3 grading scales
    if ($nb < 3) {
        var i = $nb;
        var $idNew = 1;
        for (; i < 3; i++) {
            var $type = "empty";
            addTagForm($collectionHolder, $newLinkLi, $idNew);
            $idNew++;
        }
    }

    $addTagLink.on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();
        
        // add a new tag form (see code block below)
        addTagForm($collectionHolder, $newLinkLi, 0);
    });

    function addTagForm($collectionHolder, $newLinkLi, $idNew) {
        // Get the data-prototype explained earlier
        var prototype = $collectionHolder.data('prototype');
        
        // get the new index
        var index = $collectionHolder.data('index');

// Pour modifier la valeur de l'ID créé si on n'a pas 3 input.
//        alert(index);
//        var $selector = 'innova_collecticiel_appreciation_form_gradingScales_' + index + '_scaleName';
//        alert($selector);
//        document.getElementById($selector).value = "titi";

        // Replace '$$name$$' in the prototype's HTML to
        // instead be a number based on how many items we have
        var newForm = prototype.replace(/__name__/g, index);
        
        // increase the index with one for the next item
        $collectionHolder.data('index', index + 1);

        // Display the form in the page in an li, before the "Add a tag" link li
        var $newFormLi = $('<li></li>').append(newForm);
        
        // also add a remove button, just for this example
        $newFormLi.append('<a href="#" class="remove-tag">Suppression</a>');
        
        $newLinkLi.before($newFormLi);
        
        // handle the removal, just for this example
        $('.remove-tag').click(function(e) {
            e.preventDefault();
            
            $(this).parent().remove();
            
            return false;
        });
    }
    // END ADD LINK



    // BEGIN ADD LINK
    // setup an "add a tag" link
    var $addTagLinkCriteria = $('<a href="#" class="add_tag_link">Information/critère</a>');
    var $newLinkLiCriteria = $('<li></li>').append($addTagLinkCriteria);

    // Get the ul that holds the collection of tags
    var $collectionHolderCriteria = $('ul.tags');
    
    // add the "add a tag" anchor and li to the tags ul
    $collectionHolderCriteria.append($newLinkLiCriteria);
    
    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    $collectionHolderCriteria.data('index', $collectionHolder.find(':input').length);

    // Je divise par 2 car pour UNE donnée en base, j'envoie également UNE donnée en hidden
    var $nb = ($collectionHolderCriteria.find(':input').length)/2;

    $addTagLinkCriteria.on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();
        
        // add a new tag form (see code block below)
        addTagFormCriteria($collectionHolderCriteria, $newLinkLiCriteria);
    });

    function addTagFormCriteria($collectionHolder, $newLinkLi) {
        // Get the data-prototype explained earlier
        var prototype = $collectionHolder.data('prototype');
        
        // get the new index
        var index = $collectionHolder.data('index');
        
        // Replace '$$name$$' in the prototype's HTML to
        // instead be a number based on how many items we have
        var newForm = prototype.replace(/__name__/g, index);
        
        // increase the index with one for the next item
        $collectionHolder.data('index', index + 1);
        
        // Display the form in the page in an li, before the "Add a tag" link li
        var $newFormLi = $('<li></li>').append(newForm);
        
        // also add a remove button, just for this example
        $newFormLi.append('<a href="#" class="remove-tag">Suppression</a>');
        
        $newLinkLi.before($newFormLi);
        
        // handle the removal, just for this example
        $('.remove-tag').click(function(e) {
            e.preventDefault();
            
            $(this).parent().remove();
            
            return false;
        });
    }
    // END ADD LINK



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

    //
    // Appel de cette fonction quand on affiche la liste des documents d'UN collecticiel
    // Appel pour le traitement en Ajax des états du document
    //
    $('.td_action').each(function(){

        //
        // Récupération des données, voir documentItem.html.twig.
        //
        var documentId = $(this).find('input[name="document_id"]').val();
        var isValidate = document.getElementById('document_validate_' + documentId).value;
        var commentLength = document.getElementById('document_comments_length_' + documentId).value;
        var senderId = document.getElementById('document_sender_' + documentId).value;
        var docDropUserId = document.getElementById('document_drop_user_' + documentId).value;
        var adminInnova = document.getElementById('adminInnova_' + documentId).value;
        var returnReceiptId = document.getElementById('return_receipt_' + documentId).value;
        var teacherComment = document.getElementById('teacher_comment_' + documentId).value;

        //
        // Afficher les tests ici qui permettront de rafraîchir les données.
        //
        // Reprise ici dans tests déclarés avant dans le fichier documentItem.
        //

        // delete : bouton et action "suppresion"
        // cancel : bouton et action "annulation"
        // lock : bouton et action "on ne peut rien faire"

        // Enseignant
        if (adminInnova == true) {
            if (isValidate == false || senderId != docDropUserId) {
                var selector = "#delete_" + documentId;
            }
            else if (isValidate == true && commentLength == 0 && senderId == docDropUserId) {
                var selector = "#cancel_" + documentId;
            }
            else {
                var selector = "#lock_" + documentId;
            }

            // #247 : l'élève ou l'enseignant ne peuvent rien faire s'il y a un commentaire enseignant sur le document
            // ou s'il y a un AR autre que 0.
            if (returnReceiptId > 0 || teacherComment > 0) {
                var selector = "#lock_" + documentId;
            }

        }
        // Etudiant
        if (adminInnova == false) {
            // #241 : l'élève ne peut rien faire s'il y a un AR sur le document.
            if (isValidate == false || (adminInnova == true && senderId != docDropUserId)) {
                var selector = "#delete_" + documentId;
            }
            else if (isValidate == true && adminInnova == false && commentLength == 0 && senderId == docDropUserId) {
                var selector = "#cancel_" + documentId;
            }
            else {
                var selector = "#lock_" + documentId;
            }

            // #247 : l'élève ou l'enseignant ne peuvent rien faire s'il y a un commentaire enseignant sur le document
            // ou s'il y a un AR autre que 0.
            if (returnReceiptId > 0 || teacherComment > 0) {
                var selector = "#lock_" + documentId;
            }

        }
        $(selector).css({'display': 'inline'});

    });


    // InnovaERV
    // Ajout pour le traitement des 2 actions du bouton "Action"
    $('.inputReturnReceipt').on('click', function (event) {

        var selectorDocument = "#document_id_"+$(this).attr("data-document_id"); // Extract info from data-* attributes

        var checkOneAtLeast = false;

        $("input[type='checkbox']:checked").each(
            function() {
                checkOneAtLeast = true;
//                $(selectorDocument).prop('checked', true); // Cocher la case "Valider"

                var selector = "#actionReturnReceipt";

                $("#actionReturnReceipt").removeClass("disabled"); // Ne pas pouvoir modifier cette ligne
                $("#actionReturnReceipt2").removeClass("disabled"); // Ne pas pouvoir modifier cette ligne
            });

        if (checkOneAtLeast == false) {
            $("#actionReturnReceipt").addClass("disabled"); // Ne pas pouvoir modifier cette ligne
        }

    });


    // InnovaERV
    // Ajout pour le traitement de la case Ã  cocher pour la crÃ©ation de commentaire Ã  la volÃ©e
    $('.comment_validate').on('click', function (event) {
        event.preventDefault();

        // Récupération de l'id du document
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
    // Ajout pour le traitement de la case à cocher lors de la soumission de documents
    $('#validate-modal').on('show.bs.modal', function (event) {

        var button     = $(event.relatedTarget); // Button that triggered the modal
        var documentId = button.data('document_id'); // Extract info from data-* attributes

        var senderId      = button.data('document_sender_id'); // Extract info from data-* attributes
        var commentLength = button.data('document_comment_length');
        var docDropUserId = button.data('document_dropuser_id');
        var adminInnova   = button.data('data-document_adminInnova');

        // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
        // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
        var modal = $(this);
        modal.find('#modal_confirm').attr("data-document_id", documentId); //TODO change this to use data() instead of attr()
        modal.find('#modal_confirm').attr("data-document_sender_id", senderId); //TODO change this to use data() instead of attr()
        modal.find('#modal_confirm').attr("data-document_comment_length", commentLength); //TODO change this to use data() instead of attr()
        modal.find('#modal_confirm').attr("data-document_docDropUser_id", docDropUserId); //TODO change this to use data() instead of attr()
        modal.find('#modal_confirm').attr("data-document_adminInnova", adminInnova); //TODO change this to use data() instead of attr()
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
        var senderId = $(this).attr("data-document_sender_id");
        var commentLength = $(this).attr("data-document_comment_length");
        var docDropUserId = $(this).attr("data-document_docDropUser_id"); // Extract info from data-* attributes
        var adminInnova = $(this).attr("data-document_adminInnova");

        // Ajax : appel de la route qui va mettre Ã  jour la base de donnÃ©es
        // Ajax : route "innova_collecticiel_validate_document" dans DocumentController
        var req = "#request_id_"+$(this).attr("data-document_id"); // Extract info from data-* attributes

        //
        // Afficher les tests ici qui permettront de rafraîchir les données.
        //
        if (senderId != docDropUserId) {
            var selector = "#delete_" + documentId;
        }
        else if (commentLength == 0 && senderId == docDropUserId) {
            var selector = "#cancel_" + documentId;
        }
        else {
            var selector = "#lock_" + documentId;
        }
        $(selector).css({'display': 'inline'});

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

        // bouton "OK" fermé si aucun document sélectionné
        document.getElementById('modal_confirm_return_receipt').disabled = true;
        // réinit de la valeur "pas sélectionné" sur tous les boutons des AR.
        document.getElementById('choix0').checked = false;
        document.getElementById('choix1').checked = false;
        document.getElementById('choix2').checked = false;
        document.getElementById('choix3').checked = false;
        document.getElementById('choix4').checked = false;
        document.getElementById('choix5').checked = false;
    });

    // Si je choisis un "accusé de réception" alors je réactive le bouton "OK"
    $('#choix0').on('click', function(event) {
        document.getElementById('modal_confirm_return_receipt').disabled = false;
    });
    $('#choix1').on('click', function(event) {
        document.getElementById('modal_confirm_return_receipt').disabled = false;
    });
    $('#choix2').on('click', function(event) {
        document.getElementById('modal_confirm_return_receipt').disabled = false;
    });
    $('#choix3').on('click', function(event) {
        document.getElementById('modal_confirm_return_receipt').disabled = false;
    });
    $('#choix4').on('click', function(event) {
        document.getElementById('modal_confirm_return_receipt').disabled = false;
    });
    $('#choix5').on('click', function(event) {
        document.getElementById('modal_confirm_return_receipt').disabled = false;
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

        // Récupération de l'id du document
        var dropzoneId = $(this).attr("data-dropzone_id");

        // Récupération de l'id du document
        var documentId = $(this).attr("data-document_id");

        var arrayDocsId = [];

        if (!documentId)
        {
            $("input[type='checkbox']:checked").each(
                function() {
                    var chaineCaractere = $(this).attr('id');
                    var splitChaine = chaineCaractere.split('_');
                    if (splitChaine[2] != '0') {
                        arrayDocsId.push($(this).attr('id'));
                    }
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
    // Ajout pour le traitement de la demande de commentaire : mise à jour de la table Document
    // Mise Ã  jour de la colonne "validate"
    $('.document_validate').on('click', function(event) {
    });

    // InnovaERV
    // Ajout pour le traitement de la case à cocher lors de la soumission de documents
    $('#validate-cancel-modal').on('show.bs.modal', function (event) {

        var button     = $(event.relatedTarget); // Button that triggered the modal
        var documentId = button.data('document_id'); // Extract info from data-* attributes

        var modal = $(this);
        modal.find('#modal_confirm-cancel').attr("data-document_id", documentId); //TODO change this to use data() instead of attr()
    });

    // InnovaERV
    // Appel lors de la suppression d'un document
    $('#modal_confirm-cancel').on('click', function(event) {
        event.preventDefault();
        var docId = $(this).attr("data-document_id");

        var adminInnova = $(this).attr("data-document_adminInnova");

        // Affichage du nouveau sélecteur
        var selector = "#delete_" + docId;
        $(selector).css({'display': 'inline'});

        // Je n'affiche plus l'ancien sélecteur
        var selector = "#cancel_" + docId;
        $(selector).css({'display': 'none'});

        $.ajax(
            {
                url: Routing.generate('innova_collecticiel_unvalidate_document', {documentId: docId}),
                method: "POST",
                data:
                {
                    adminInnova: adminInnova
                },
                complete: function(data) {
                    $("#is-validate-" + docId).html(data.responseText);
                }
            }
        );

        // Fermeture de la modal
        $('#validate-cancel-modal').modal('hide');

    });

    // InnovaERV : sélection et déselection dans la liste des demandes adressées.
    $('#document_id_0').on('click', function(event) {
        if($(this).is(':checked')){
            $('input[type=checkbox]').each(function(i,k){
                $("#actionReturnReceipt").removeClass("disabled"); // Ne pas pouvoir modifier cette ligne
                $("#actionReturnReceipt2").removeClass("disabled"); // Ne pas pouvoir modifier cette ligne
                $(k).prop('checked',true);
              })
        }
        else
        {
            $('input[type=checkbox]').each(function(i,k){
                $("#actionReturnReceipt").addClass("disabled"); // Ne pas pouvoir modifier cette ligne
                $("#actionReturnReceipt2").addClass("disabled"); // Ne pas pouvoir modifier cette ligne
                $(k).prop('checked',false);
            })
        }
    })

    $('input[type=checkbox]').not('#document_id_0').click(function() {
        $('#document_id_0').prop('indeterminate', true);
    })

    // InnovaERV : ajout du bouton "Retour" dans la liste des commentaires.
    // InnovaERV : ajout de la redirection via Ajax.
    $('.backLink').on('click', function(event) {
        event.preventDefault();

        var dropzoneId = $(this).attr("data-resource_id");

        $.ajax({
            url: Routing.generate('innova_collecticiel_back_link',
                {
                dropzoneId: dropzoneId,
                }),
            method: "GET",
            data:
            {
            },
            complete : function(data) {
                var data_link = $.parseJSON(data.responseText)

                if (data_link !== 'false') {
                    document.location.href=data_link.link;
                }

            }
        });

    });

    // Pour changer et traduire le message "Veuillez renseigner ce champ."
    $('#submitTitle').on('click', function(event) {

        var text = null;

        // Input
        text = document.getElementById('innova_collecticiel_document_file_form_text').value;

        // Récupération de la zone traduite
        var translation = document.getElementById('translation_id').value;

        if (text.length == 0) {

            // Afficher la zone traduite
            document.getElementById("innova_collecticiel_document_file_form_text").setCustomValidity(translation);
            return true;

        } else {
             document.getElementById("innova_collecticiel_document_file_form_text").setCustomValidity('');
        }

        // Textarea
        var doc = tinyMCE.get('innova_collecticiel_document_file_form_document').getContent();
        var translation_doc_id = document.getElementById('translation_doc_id').value;

        if (doc.length == 0) {
            // Afficher la zone traduite
            document.getElementById("innova_collecticiel_document_file_form_document").setCustomValidity(translation_doc_id);
        } else {
            document.getElementById("innova_collecticiel_document_file_form_document").setCustomValidity('');
        }

    });

    // Pour changer et traduire le message "Veuillez renseigner ce champ."
    $('#innova_collecticiel_document_file_form_document').on('click', function(event) {

        var doc = document.getElementById('innova_collecticiel_document_file_form_document').value;
        // Récupération de la zone traduite
        var translation = document.getElementById('translation_id').value;

        // var $elem = $("#email");
        // var email = $elem.val();

        // //the ajax call returns true if the email exists
        // $.get( "ajax/checkUniqueEmail", function(data) {
        //     if(data === "true"){
        //         $elem.setCustomValidity("This email already exists.");
        //     }else{
        //         $elem.setCustomValidity("")
        //     }
        //     //then we submit the form
        //     $("#form").submit();
        // });
    });

    // Pour changer et traduire le message "Veuillez renseigner ce champ."
//    $('#innovaDocument').on('click', function(event) {
//alert("placeholder document3");
//                $("#innova_collecticiel_document_file_form_document").tinymce().focus();
//                tinyMCE.get("innova_collecticiel_document_file_form_document").getBody().focus();
//    });



//    $('#innovaDocument').find('textarea').val('Some default Text');

});
