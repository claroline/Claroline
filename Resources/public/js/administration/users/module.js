(function () {
    'use strict';

    var UsersManager = angular.module('UsersManager', [
        'genericSearch',
        'data-table',
        'ui.bootstrap.tpls',
        'clarolineAPI',
        'ui.translation',
        'ui.router',
        'ncy-angular-breadcrumb'
    ]);

    var translate = function(key) {
        return translator.trans(key, {}, 'platform');
    }

    UsersManager.config(function($stateProvider, $urlRouterProvider) {
        $stateProvider
            .state(
                'administration.users',
                {
                    url: "/users",
                    templateUrl: function($stateParam) {
                        return AngularApp.webDir +
                            'bundles/clarolinecore/js/administration/users/Partial/user_content.html';
                    },
                    ncyBreadcrumb: {
                        label: translate('user_list')
                    },
                    controller: 'UserController',
                    controllerAs: 'uc'
                }
            )
            .state(
                'administration.groups',
                {
                    url: "/groups",
                    templateUrl: function($stateParam) {
                        return AngularApp.webDir + 'bundles/clarolinecore/js/administration/groups/Partial/group_manager.html';

                    },
                    controller: 'GroupController',
                    controllerAs: 'gc',
                    data: {
                        'pageTitle': translate('group_list')
                    },
                    ncyBreadcrumb: {
                        label: translate('group_list')
                    }
                }
            )
        ;

        $urlRouterProvider.otherwise("/users");
    });
})();