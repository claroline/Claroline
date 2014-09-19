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
        console.log('open called');
        // change button layout
        $(this).children('span:first').remove();
        var message = Translator.get('innova_tools:create_path_from_model_loading');
        $(this).text(message);
        // disable button
        $(this).prop('disabled', 'disabled');
        // get workspace id to genrate route
        var wId = $("#workspaceId").val();
        
        // url for ajax method to retrieve all path models 
        var url = Routing.generate('innova_path_template_list');        
        $.ajax({
            type: "GET",
            url: url,
            success: function(data){
                // empty modal path model list
                $('#dd_paths_template option').remove();
                // add the first option
                var optionText = Translator.get('innova_tools:empty_option');
                 $('#dd_paths_template').append('<option value="-1">' + optionText + '</option>');
                // populate list with path models
                for(var i in data){
                    var linkUrl = Routing.generate('innova_path_editor_create_from_template', {workspaceId: wId, templateId:data[i].id});
                    var listItem = '<a href="'+linkUrl+'" class="list-group-item">' + data[i].name + '</a>';   
                    $('#template-list').append(listItem);
                }
                // show modal
                $('#newPathFromModel').modal('show');
            },
            error: function(e){
                console.log(e);
            }
        });        

    });
    
    // reset button 
    $('#newPathFromModel').on('hidden.bs.modal', function () {      
        $('.path-from-model').prop('disabled', ''); 
        var message = Translator.get('innova_tools:create_path_from_model') ;
        $('.path-from-model').html('<span class="fa fa-plus"></span> ' + message);
    });
});