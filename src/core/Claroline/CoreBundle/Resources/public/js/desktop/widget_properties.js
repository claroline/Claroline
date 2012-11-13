(function(){

    $('.chk-config-visible').on('change', function(e){
        var displayConfigId = e.currentTarget.parentNode.parentNode.dataset.id;
        var widgetId = e.currentTarget.parentNode.parentNode.dataset.widgetId;
        console.debug(e);
        var route = Routing.generate('claro_desktop_widget_invertvisible', {'displayConfigId': displayConfigId, 'widgetId': widgetId });
        ClaroUtils.sendRequest(route, undefined, undefined, 'POST');
    })

})()

