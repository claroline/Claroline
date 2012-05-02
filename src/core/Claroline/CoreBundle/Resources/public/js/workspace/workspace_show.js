
$(function(){
    twigWorkspaceId = document.getElementById('twig_attributes').getAttribute('data-workspaceId');
    var params = {
        submitHandler: function(oForm, id){
                addResource(id, twigWorkspaceId);
            }
        };
        
    picker = $('#filepicker').claroFilePicker(params);
    
    $('#show_filepicker_button').click(function(){
        picker.modal("show");
    });
}); 

function addResource(resourceId, workspaceId)
{
$.ajax({
    type: 'POST',
    url: Routing.generate('claro_resource_add_workspace', {'resourceId':resourceId,'workspaceId':workspaceId}),
    cache: false,
    success: function(data){
            alert(data);
            $('#div_resource').append("append something");
        },
    error: function(xhr){
        alert(xhr.status);
    }});
}
