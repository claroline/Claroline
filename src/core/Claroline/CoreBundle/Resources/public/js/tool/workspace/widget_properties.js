(function(){

    $('.chk-config-visible').on('change', function(e){
        var displayConfigId = e.currentTarget.parentNode.parentNode.dataset.id;
        var widgetId = e.currentTarget.parentNode.parentNode.dataset.widgetId;
        var workspaceId = e.currentTarget.parentNode.parentNode.parentElement.dataset.workspaceId;
        var route = Routing.generate('claro_workspace_widget_invertvisible', {'displayConfigId': displayConfigId, 'widgetId': widgetId, 'workspaceId': workspaceId});
        Claroline.Utilities.ajax({url: route, type: 'POST'});
    })

})()

