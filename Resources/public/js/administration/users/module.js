(function () {
    'use strict';

    var UsersManager = angular.module('UsersManager', [
        'genericSearch',
        'data-table',
        'ui.bootstrap.tpls',
        'clarolineAPI',
        'ui.translation',
        'ui.router',
        'GroupsManager',
        'ncy-angular-breadcrumb'
    ]);

    var translate = function(key) {
        return translator.trans(key, {}, 'platform');
    }

    UsersManager.config(function($stateProvider, $urlRouterProvider) {
        $stateProvider
            .state ('users', {
                abstract: true,
                url: '/users',
                templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/administration/users/Partial/main.html',

            })
            .state(
                'users.list',
                {
                    url: "",
                    ncyBreadcrumb: {
                        label: translate('user_list')
                    },
                    views: {
                        'users': {
                            templateUrl: function($stateParam) {
                                return AngularApp.webDir +
                                    'bundles/clarolinecore/js/administration/users/Partial/user_content.html';
                            },
                            controller: 'UserController',
                            controllerAs: 'uc'
                        }
                    }
                }
            )
            .state(
                'users.groups',
                {
                    abstract: true,
                    url: "/groups",
                    ncyBreadcrumb: {
                        label: translate('group_list'),
                        parent: 'users.list'
                    },
                    views: {
                        'groups': {
                            templateUrl: function($stateParam) {
                                return AngularApp.webDir + 'bundles/clarolinecore/js/administration/groups/Partial/main.html';
                            }
                        }
                    }
                }
            )
        ;

        $urlRouterProvider.otherwise("/users");
    });
})();