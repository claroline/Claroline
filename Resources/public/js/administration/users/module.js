(function () {
    'use strict';

    var UsersManager = angular.module('UsersManager', [
        'genericSearch',
        'data-table',
        'ui.bootstrap.tpls',
        'clarolineAPI',
        'groupsManager',
        'ngRoute',
        'ui.translation'
    ]);

    UsersManager.config(['$routeProvider',
        function($routeProvider) {
            $routeProvider.
                when('/users/list', {
                    templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/administration/users/views/user_main.html',
                    controller: 'UsersCtrl',
                    controllerAs: 'uc'
                }).
                when('/groups/list', {
                    templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/administration/groups/views/group_main.html',
                    controller: 'GroupsCtrl',
                    controllerAs: 'uc'
                }).
                otherwise({
                    redirectTo: '/users/list'
                });
        }]);
})();