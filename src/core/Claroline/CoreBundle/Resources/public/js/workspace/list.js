(function(){
    $('.link-delete-workspace').click(function(e){
         var route = Routing.generate('claro_workspace_delete', {'workspaceId': $(this).attr('data-workspace-id')});
         var row = $(this).parent();
         Claroline.Utilities.ajax({
            url: route,
            success: function(data){
                row.remove();
            },
            type: 'DELETE'
         });
    });
})()
