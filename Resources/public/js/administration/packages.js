$('.install-pkg').on('click', function(event) {
    event.preventDefault();
    var date = new Date().getTime();
    var bundle = $(event.target).attr('data-package-name');
    var version = $(event.target).attr('data-package-version');
    var url = Routing.generate('claro_admin_plugin_install', {
        'bundle': $(event.target).attr('data-package-name'),
        'date': date
    });
    var refreshUrl = Routing.generate('claro_admin_plugins');
    var html = Twig.render(PackageLog, {'bundle': bundle, 'version': version});

    var waitingHandler = function() {
        $('#log-content').show();
        var logFile = Routing.generate('claro_admin_plugins_log', {'date': date});
        var logFile = $('#log-content').attr('href') + '?logFile=' + 'update-' + date + '.log';
        //alert (logFile);
        var logDisplayer = new window.Claroline.LogDisplayer.Displayer('#log-content');
        logDisplayer.setLogFile(logFile);
        logDisplayer.start();
    };

    var errorHandler = function(logDisplayer) {
        //location.reload();
    };

    var successHandler = function(logDisplayer) {
        //window.Claroline.LogDisplayer.endAll();
    }

    var modal = window.Claroline.Modal.confirmRequest(
        url,
        successHandler,
        undefined,
        html,
        Translator.trans('package_install', {}, 'platform'),
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
