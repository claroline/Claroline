import angular from 'angular/index'
import {} from '../../modal/module'
import {} from 'angular-loading-bar'
import {} from 'angular-ui-translation/angular-translation'
import {} from '../../fos-js-router/module'
import {} from '../../asset/module'
import register from '../../register/register'
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