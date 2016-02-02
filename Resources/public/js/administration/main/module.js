(function () {
    'use strict';

    var AdministrationModule = angular.module('AdministrationModule', [
        'ui.router',
        'ncy-angular-breadcrumb',
        'UsersManager',
        'GroupsManager'
    ]);

    var translate = function(key) {
        return translator.trans(key, {}, 'platform');
    }

    AdministrationModule.config(function($stateProvider, $urlRouterProvider) {
        $stateProvider
            .state(
                'administration',
                {
                    url: "/administration",
                    templateUrl: function($stateParam) {
                        return AngularApp.webDir +
                            'bundles/clarolinecore/js/administration/main/Partial/main.html';
                    },
                    ncyBreadcrumb: {
                        label: translate('administration')
                    }

                }
            )
        ;

        $urlRouterProvider.otherwise("/administration");
    });
})();