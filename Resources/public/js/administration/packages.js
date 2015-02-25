$('.install-pkg').on('click', function(event) {
    event.preventDefault();
    var date = new Date().getTime();
    var url = Routing.generate('claro_admin_plugin_install', {
        'bundle': $(event.target).attr('data-package-name'),
        'date': date
    });
    var refreshUrl = Routing.generate('claro_admin_plugins');
    var html = Twig.render(PackageLog);

    var waitingHandler = function() {
        $('#log-content').show();
        var logDisplayer = new window.Claroline.LogDisplayer.Displayer('#log-content');
        logDisplayer.setLogFile(Routing.generate('claro_admin_plugins_log', {'date': date}));
        logDisplayer.start();
    };

    var errorHandler = function(logDisplayer) {
        location.reload();
    };

    var successHandler = function(logDisplayer) {
        location.reload();
    }

    var modal = window.Claroline.Modal.confirmRequest(
        url,
        successHandler,
        undefined,
        html,
        Translator.trans('package_install_confirm', {}, 'platform'),
        waitingHandler,
        undefined,
        errorHandler,
        undefined,
        true
    );
/*
    $('#logs-btn').on('click', function() {
        alert('uolo');
        $('#log-content').show();
    });*/
});
