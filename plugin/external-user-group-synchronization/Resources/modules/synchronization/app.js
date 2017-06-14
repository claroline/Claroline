import angular from 'angular/index'
import uiRouter from 'angular-ui-router'
import {} from 'angular-bootstrap'
import {} from 'angular-loading-bar'
import {} from 'angular-ui-translation/angular-translation'
import {} from '#/main/core/fos-js-router/module'
import { SearchListModule } from './utils/search-list/search-list.module'
import { SynchronizationAppComponent } from './app.component'
import { UserListComponent } from './user/user-list.component'
import { UserListService } from './user/user-list.service'
import { GroupListComponent } from './group/group-list.component'
import { GroupListService } from './group/group-list.service'

import { syncRouterConfig } from './app.router'

export const SynchronizationApp = angular
  .module('app.synchronization', [
    uiRouter,
    SearchListModule,
    'angular-loading-bar',
    'ui.fos-js-router',
    'ui.translation',
    'ui.bootstrap'
  ])
  .component('syncApp', SynchronizationAppComponent)
  .component('userList', UserListComponent)
  .component('groupList', GroupListComponent)
  .service('UserListService', UserListService)
  .service('GroupListService', GroupListService)
  .value('externalSource', window.externalSource)
  .value('platformRoles', window.platformRoles)
  .config(syncRouterConfig)
  .name

//Bootstrap angular in body
angular.element(document).ready(function () {
  angular.bootstrap(document.getElementsByTagName('body')[ 0 ], [ 'app.synchronization' ])
})