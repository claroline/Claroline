$(document).ready(function () {
    $('button, a').tooltip({placement: 'top'});

    $('.path-delete').confirmModal({
        confirmTitle: 'Confirmation',
        confirmMessage: 'Pouvez-vous confirmer l\'action de suppression ?',
        confirmOk: 'OK',
        confirmCancel: 'Annuler',
        confirmDirection: 'ltr',
        confirmStyle: 'primary',
        confirmCallback: submitForm
    });

    function submitForm(target) {
        $(target).parent('form').submit();
    }

    $('.path-from-model').on('click', function () {
        // get workspace id to generate route
        var wId = $("#workspace-id").val();

        // change button layout
        $(this).children('span:first').remove();

        var message = Translator.trans('create_path_from_model_loading', {}, 'innova_tools');
        $(this).text(message);

        // disable button
        $(this).prop('disabled', 'disabled');

        // empty modal path model list
        $('#template-list a').remove();

        // url for ajax method
        var url = Routing.generate('innova_path_template_list');
        $.ajax({
            type: "GET",
            url: url,
            success: function(data){
                // populate modal with path templates
                for(var i in data){
                    var linkUrl = Routing.generate('innova_path_editor_create_from_template', {workspaceId: wId, templateId:data[i].id});
                    var listItem = '<a href="' + linkUrl + '" class="list-group-item">' + data[i].name + '</a>';   
                    $('#template-list').append(listItem);
                }
                // show modal
                $('#path-template-list-modal').modal('show');
            },
            error: function(e){
                console.log(e);
            }
        });
    });
    
    // modal closed 
    $('#path-template-list-modal').on('hidden.bs.modal', function () { 
        $('.path-from-model').prop('disabled', ''); 
        var message = Translator.trans('create_path_from_model', {}, 'innova_tools') ;
        $('.path-from-model').html('<span class="fa fa-plus"></span> ' + message);
    });
});