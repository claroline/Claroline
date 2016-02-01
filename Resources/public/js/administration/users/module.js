(function () {
    'use strict';

    var UsersManager = angular.module('UsersManager', [
        'genericSearch',
        'data-table',
        'ui.bootstrap.tpls',
        'clarolineAPI',
        'GroupsManager',
        'ui.translation',
        'ui.router'
    ]);

    UsersManager.config(function($stateProvider, $urlRouterProvider) {
        console.log($stateProvider);
        $stateProvider
            .state(
                'users',
                {
                    url: "/users",
                    templateUrl: function($stateParam) {
                        return AngularApp.webDir +
                            'bundles/clarolinecore/js/administration/users/Partial/user_content.html';
                    }

                }
            )
            .state(
                'groups',
                {
                    url: "/groups",
                    templateUrl: function($stateParam) {
                        return AngularApp.webDir + 'bundles/clarolinecore/js/administration/groups/Partial/group_manager.html';

                    },
                    controller: 'GroupController',
                    controllerAs: 'gc'
                }
            )
        ;

        $urlRouterProvider.otherwise("/users");
    });
})();