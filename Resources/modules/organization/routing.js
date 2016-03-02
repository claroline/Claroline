export default function($stateProvider, $urlRouterProvider) {
    const translate = function(key) {
        return window.Translator.trans(key, {}, 'platform');
    }

    $stateProvider
        .state ('organizations', {
            abstract: true,
            url: '/organizations',
            template: require('./Partial/main.html')

        })
        .state(
            'organizations.list',
            {
                url: '',
                ncyBreadcrumb: {
                    label: translate('organizations')
                },
                views: {
                    'organizations': {
                        template: require('./Partial/organizations_main.html'),
                        controller: 'OrganizationController',
                        controllerAs: 'oc'
                    }
                }
            }
        )

        .state(
            'organizations.locations',
            {
                url: '/locations',
                ncyBreadcrumb: {
                    label: translate('locations'),
                    parent: 'organizations.list'
                },
                views: {
                    'locations': {
                        template: require('./../location/Partial/locations_main.html'),
                        controller: 'LocationController',
                        controllerAs: 'lc'
                    }
                }
            }
        )

    $urlRouterProvider.otherwise('/organizations')
}
