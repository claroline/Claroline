(function () {

    $('.chk-admin-lock').on('change', function(e){
        var id = e.currentTarget.parentNode.parentNode.dataset.id;
        var route = Routing.generate('claro_admin_invert_widgetconfig_lock', {'displayConfigId': id});
        ClaroUtils.sendRequest(route, undefined, undefined, 'POST');
    });

    $('.chk-config-visible').on('change', function(e){
        var id = e.currentTarget.parentNode.parentNode.dataset.id;
        var route = Routing.generate('claro_admin_invert_widgetconfig_visible', {'displayConfigId': id});
        ClaroUtils.sendRequest(route, undefined, undefined, 'POST');
    })
})();