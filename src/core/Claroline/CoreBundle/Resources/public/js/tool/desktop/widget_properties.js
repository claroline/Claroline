(function(){

    $('.chk-config-visible').on('change', function(e){
        var displayConfigId = e.currentTarget.parentNode.parentNode.dataset.id;
        var widgetId = e.currentTarget.parentNode.parentNode.dataset.widgetId;
        var route = Routing.generate('claro_desktop_widget_invertvisible', {'displayConfigId': displayConfigId, 'widgetId': widgetId });
        Claroline.Utilities.ajax({url:route, type:'POST'});
    })

})()

