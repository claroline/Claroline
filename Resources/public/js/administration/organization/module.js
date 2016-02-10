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
            .state ('organizations', {
                abstract: true,
                url: '/organizations',
                templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/administration/organization/Partial/main.html',

            })
            .state(
                'organizations.list',
                {
                    url: "",
                    ncyBreadcrumb: {
                        label: translate('organizations')
                    },
                    views: {
                        'organizations': {
                            templateUrl: function($stateParam) {
                                return AngularApp.webDir + 'bundles/clarolinecore/js/administration/organization/Partial/organizations_main.html'
                            },
                            controller: 'OrganizationController',
                            controllerAs: 'oc'
                        }
                    }
                }
            )
            
            .state(
                'organizations.locations',
                {
                    url: "/locations",
                    ncyBreadcrumb: {
                        label: translate('locations'),
                        parent: 'organizations.list'
                    },
                    views: {
                        'locations': {
                            templateUrl: function($stateParam) {
                                return AngularApp.webDir + 'bundles/clarolinecore/js/administration/location/Partial/locations_main.html'
                            },

                        controller: 'LocationController',
                        controllerAs: 'lc'
                        }
                    }
                }
            )
        ;


        $urlRouterProvider.otherwise("/organizations");
    });
})();