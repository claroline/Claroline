export default function($stateProvider, $urlRouterProvider) {        
    $stateProvider
        .state ('registration_main_menu', {
            url: '/registration/main/menu',
            template: require('./Cursus/Partial/cursus_registration_main_menu.html')
        })
        .state ('registration_cursus_list', {
            url: '/registration/cursus/list',
            template: require('./Cursus/Partial/cursus_registration_cursus_list.html'),
            controller: 'CursusRegistrationCtrl',
            controllerAs: 'crc'
        })
        .state ('registration_searched_cursus_list', {
            url: '/registration/searched/cursus/{search}',
            template: require('./Cursus/Partial/cursus_registration_searched_cursus_list.html'),
            controller: 'CursusRegistrationSearchCtrl',
            controllerAs: 'crsc'
        })
        .state ('registration_cursus_management', {
            url: '/registration/cursus/{cursusId}/management',
            template: require('./Cursus/Partial/cursus_registration_cursus_management.html'),
            controller: 'CursusRegistrationManagementCtrl',
            controllerAs: 'crmc'
        })
        .state ('registration_queue_management', {
            url: '/registration/queue/management',
            template: require('./Queue/Partial/cursus_queue_management.html'),
            controller: 'CursusQueueManagementCtrl',
            controllerAs: 'cqmc'
        })

    $urlRouterProvider.otherwise('/registration/main/menu')
}
