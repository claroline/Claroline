(function () {
    'use strict';

    var translate = function(key) {
        return translator.trans(key, {}, 'platform');
    }

    var OrganizationManager = angular.module('OrganizationManager', [
        'ui.router',
        'ui.tree',
        'clarolineAPI',
        'ui.bootstrap.tpls',        
        'LocationManager',
        'ui.translation',
        'ncy-angular-breadcrumb'
    ]);

    OrganizationManager.config(function($stateProvider, $urlRouterProvider) {
        $stateProvider
            .state(
                'organizations',
                {
                    url: "/organizations",
                    templateUrl: function($stateParam) {
                        return AngularApp.webDir + 'bundles/clarolinecore/js/administration/organization/Partial/organizations_main.html'
                    },
                    ncyBreadcrumb: {
                        label: translate('organizations')
                    },
                    controller: 'OrganizationController',
                    controllerAs: 'oc'
                }
            )
            .state(
                'organizations.locations',
                {
                    url: "/locations",
                    templateUrl: function($stateParam) {
                        return AngularApp.webDir + 'bundles/clarolinecore/js/administration/location/Partial/locations_main.html'
                    },
                    ncyBreadcrumb: {
                        label: translate('locations')
                    },
                    controller: 'LocationController',
                    controllerAs: 'lc'
                }
            )
        ;

        $urlRouterProvider.otherwise("/organizations");
    });
})();