$('.install-pkg').on('click', function(event) {
    event.preventDefault();
    var date = new Date().getTime();
    var bundle = $(event.target).attr('data-package-name');
    var version = $(event.target).attr('data-package-version');
    var url = Routing.generate('claro_admin_plugin_install', {
        'bundle': $(event.target).attr('data-package-name'),
        'date': date
    });

    var html = Twig.render(PackageLog, {'bundle': bundle, 'version': version});

    var waitingHandler = function() {
        $('#log-content').show();
        //var logFile = Routing.generate('claro_admin_plugins_log', {'date': date});
        var logFile = $('#log-content').attr('href') + '?logFile=' + 'update-' + date + '.log';
        alert (logFile);
        var logDisplayer = new window.Claroline.LogDisplayer.Displayer('#log-content');
        logDisplayer.setLogFile(logFile);
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
        Translator.trans('package_install', {}, 'platform'),
        waitingHandler,
        undefined,
        errorHandler,
        undefined,
        true,
        true
    );

    $('.btn-modal-confirm').on('click', function(e) {
        modal.on('hide.bs.modal', function(e) {
            e.preventDefault();
        });
        $('.modal-footer').hide();
        $('#package-confirm-msg').hide();
    });
});

$('.refresh-platform').on('click', function(event) {
    var date = new Date().getTime();
    var url = Routing.generate('claro_admin_refresh', {
        'date': date
    });
    var html = Twig.render(RefresherLog, {});

    var errorHandler = function(logDisplayer) {
        location.reload();
    };

    var successHandler = function(logDisplayer) {
        location.reload();
    }

    var waitingHandler = function() {
        $('#log-content').show();
        //var logFile = Routing.generate('claro_admin_plugins_log', {'date': date});
        var logFile = $('#log-content').attr('href') + '?logFile=' + 'refresh-' + date + '.log';
        var logDisplayer = new window.Claroline.LogDisplayer.Displayer('#log-content');
        logDisplayer.setLogFile(logFile);
        logDisplayer.start();
    };

    var modal = window.Claroline.Modal.confirmRequest(
        url,
        successHandler,
        undefined,
        html,
        Translator.trans('platform_refresh', {}, 'platform'),
        waitingHandler,
        undefined,
        errorHandler,
        undefined,
        true,
        true
    );

    //it's dirty but I'm lazy.
    $('.btn-modal-confirm').on('click', function(e) {
        modal.on('hide.bs.modal', function(e) {
            e.preventDefault();
        });
        $('.modal-footer').hide();
        $('#package-confirm-msg').hide();
        $('#refresher-confirm-msg').hide();
    });
});
