(function () {
    'use strict';

    var translate = function(key) {
        return translator.trans(key, {}, 'platform');
    }

    var GroupsManager = angular.module('GroupsManager', [
        'genericSearch',
        'data-table',
        'ui.router',
        'ncy-angular-breadcrumb'
    ]);

    GroupsManager.config(function($stateProvider, $urlRouterProvider) {
        
        $stateProvider
            .state(
                'users.groups.list',
                {
                    url: "",
                    ncyBreadcrumb: {
                        label: translate('group_list'),
                        parent: 'users.list'
                    },
                    views: {
                        'groups': {
                            templateUrl: function($stateParam) {
                                return AngularApp.webDir + 'bundles/clarolinecore/js/administration/groups/Partial/group_manager.html';

                            },
                            controller: 'GroupController',
                            controllerAs: 'gc'
                        }
                    }
                }
            )
            .state(
                'users.groups.users',
                {
                    url: "/{groupId}",
                    ncyBreadcrumb: {
                        label: translate('users'),
                        parent: 'users.groups.list'
                    },
                    views: {
                        'users': {
                            templateUrl: function($stateParam) {
                                return AngularApp.webDir +
                                    'bundles/clarolinecore/js/administration/groups/Partial/group_show_users.html';
                            },
                            controller: 'UserListController',
                            controllerAs: 'ulc'
                        }
                    }
                }
            )
        ;
    });
})();