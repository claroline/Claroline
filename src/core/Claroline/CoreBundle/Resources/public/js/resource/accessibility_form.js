$(function(){
    var parentId = document.getElementById('claro_data').getAttribute('data-parent_id');
    var action = $('#generic_form').attr('action');
    action = action.replace('_instanceId', parentId);
    $('#generic_form').submit(function(e){
        e.preventDefault();
        var id = $('#generic_form').attr('id');
        ClaroUtils.sendForm(action, document.getElementById(id), submissionHandler);
        window.location = Routing.generate('claro_resource_accessibility_manager', {'parentId':parentId})
    })

    var submissionHandler = function(){
        //Verifications go here.
        //If wrong, display the form with erros.
    }
});