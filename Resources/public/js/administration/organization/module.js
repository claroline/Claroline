(function () {
    'use strict';

    var module = angular.module('OrganizationManager', [
        'ui.tree',
        'clarolineAPI',
        'ui.bootstrap',
        'ui.bootstrap.tpls',
        'ngRoute',
        'LocationManager'
    ]);

    module.config(['$routeProvider',
        function($routeProvider) {
            $routeProvider.
                when('/organizations', {
                    templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/administration/organization/Partial/organizations_main.html',
                    controller: 'OrganizationController',
                    controllerAs: 'oc'
                }).
                when('/locations', {
                    templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/administration/location/Partial/locations_main.html',
                    controller: 'LocationController',
                    controllerAs: 'lc'
                }).
                otherwise({
                    redirectTo: '/organizations'
                });
        }]);
})();