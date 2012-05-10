
$(function(){
    twigWorkspaceId = document.getElementById('twig_attributes').getAttribute('data-workspaceId');
    var params = {
        submitHandler: function(oForm, id){
                var option =  getCheckedValue(oForm["options"]);
                addResource(id, twigWorkspaceId, option);
            }
        };
        
    picker = $('#filepicker').claroFilePicker(params);
    
    $('#show_filepicker_button').click(function(){
        picker.modal("show");
    });
}); 

function addResource(resourceId, workspaceId, option)
{
$.ajax({
    type: 'POST',
    url: Routing.generate('claro_resource_add_workspace', {'resourceId':resourceId,'workspaceId':workspaceId, 'option':option}),
    cache: false,
    success: function(data){
            alert(data);
            $('#div_resource').append("append something");
        },
    error: function(xhr){
        alert(xhr.status);
    }});
}

function getCheckedValue(radioObj) {
	if(!radioObj)
		return "";
	var radioLength = radioObj.length;
	if(radioLength == undefined)
		if(radioObj.checked)
			return radioObj.value;
		else
			return "";
	for(var i = 0; i < radioLength; i++) {
		if(radioObj[i].checked) {
			return radioObj[i].value;
		}
	}
	return "";
}
