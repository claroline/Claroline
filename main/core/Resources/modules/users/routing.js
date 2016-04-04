export default function($stateProvider, $urlRouterProvider) {
    const translate = function(key) {
        return window.Translator.trans(key, {}, 'platform');
    }

    $stateProvider
        .state ('users', {
            abstract: true,
            url: '/users',
            template: require('./Partial/main.html')
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
                        template: require('./Partial/user_content.html'),
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
                        template: require('./../groups/Partial/main.html')
                    }
                }
            }
        )

    $urlRouterProvider.otherwise('/users');
}
