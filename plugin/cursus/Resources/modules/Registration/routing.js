import mainMenuTemplate from './Cursus/Partial/cursus_registration_main_menu.html'
import cursusListTemplate from './Cursus/Partial/cursus_registration_cursus_list.html'
import searchedCursusListTemplate from './Cursus/Partial/cursus_registration_searched_cursus_list.html'
import cursusManagementTemplate from './Cursus/Partial/cursus_registration_cursus_management.html'
import queueManagementTemplate from './Queue/Partial/cursus_queue_management.html'

export default function ($stateProvider, $urlRouterProvider) {
  $stateProvider
    .state ('registration_main_menu', {
      url: '/registration/main/menu',
      template: mainMenuTemplate
    })
    .state ('registration_cursus_list', {
      url: '/registration/cursus/list',
      template: cursusListTemplate,
      controller: 'CursusRegistrationCtrl',
      controllerAs: 'crc'
    })
    .state ('registration_searched_cursus_list', {
      url: '/registration/searched/cursus/{search}',
      template: searchedCursusListTemplate,
      controller: 'CursusRegistrationSearchCtrl',
      controllerAs: 'crsc'
    })
    .state ('registration_cursus_management', {
      url: '/registration/cursus/{cursusId}/management',
      template: cursusManagementTemplate,
      controller: 'CursusRegistrationManagementCtrl',
      controllerAs: 'crmc'
    })
    .state ('registration_queue_management', {
      url: '/registration/queue/management',
      template: queueManagementTemplate,
      controller: 'CursusQueueManagementCtrl',
      controllerAs: 'cqmc'
    })

  $urlRouterProvider.otherwise('/registration/main/menu')
}
