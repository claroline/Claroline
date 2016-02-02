(function () {
    'use strict';

    var GroupsManager = angular.module('GroupsManager', [
        'genericSearch',
        'data-table',
        'ui.router'
    ]);

    GroupsManager.config(function($stateProvider, $urlRouterProvider) {
        $stateProvider
            .state(
                'list',
                {
                    url: "/list",
                    templateUrl: function($stateParam) {
                        return AngularApp.webDir +
                            'bundles/clarolinecore/js/administration/groups/Partial/group_list.html';
                    }
                }
            )
            .state(
                'groups.users',
                {
                    url: "/users",
                    templateUrl: function($stateParam) {
                        return AngularApp.webDir +
                            'bundles/clarolinecore/js/administration/groups/Partial/group_show_users.html';
                    }

                }
            );

        $urlRouterProvider.otherwise("/list");
    });
})();