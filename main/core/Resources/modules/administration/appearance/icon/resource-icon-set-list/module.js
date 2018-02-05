import angular from 'angular/index'
import 'angular-loading-bar'
import '#/main/core/innova/angular-translation'

import {} from '#/main/core/modal/module'
import {} from '#/main/core/api/router/module'
import {} from '#/main/core/scaffolding/asset/module'
import register from '#/main/core/register/register'
import resourceIconSetList from './resource-icon-set-list.directive'
import resourceIconSetService from './resource-icon-set-list.service'

let resourceIconSetListApp =  new register(
  'resourceIconSetListApp',
  [
    'angular-loading-bar',
    'ui.modal',
    'ui.asset',
    'ui.bootstrap',
    'ui.fos-js-router',
    'ui.translation'
  ])

resourceIconSetListApp
  .directive('resourceIconSetList', resourceIconSetList)
  .service('resourceIconSetService', resourceIconSetService)
  .value('iconSets', window.iconSets)

//Bootstrap angular in body
angular.element(document).ready(function () {
  angular.bootstrap(document.getElementsByTagName('body')[ 0 ], [ 'resourceIconSetListApp' ])
})