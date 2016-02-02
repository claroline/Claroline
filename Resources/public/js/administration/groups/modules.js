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
                'administration.groups.users',
                {
                    url: "/{groupId}",
                    templateUrl: function($stateParam) {
                        return AngularApp.webDir +
                            'bundles/clarolinecore/js/administration/groups/Partial/group_show_users.html';
                    },
                    ncyBreadcrumb: {
                        label: translate('users')
                    },
                    controller: 'UserListController',
                    controllerAs: 'ucl'
                }
            );
    });
})();