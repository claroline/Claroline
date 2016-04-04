export default function($stateProvider, $urlRouterProvider) {
    const translate = function(key) {
        return window.Translator.trans(key, {}, 'platform');
    }

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
                        template: require('./Partial/group_manager.html'),
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
                        template: require('./Partial/group_show_users.html'),
                        controller: 'UserListController',
                        controllerAs: 'ulc'
                    }
                }
            }
        )
    ;
}
