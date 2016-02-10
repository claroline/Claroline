(function () {
    'use strict';

    var translate = function(key) {
        return translator.trans(key, {}, 'platform');
    }

    var LocationManager = angular.module('LocationManager', [
        'clarolineAPI',
        'ui.bootstrap.tpls',
        'ui.translation',
        'data-table',
        'ui.router',
        'ncy-angular-breadcrumb'
    ]);

})();