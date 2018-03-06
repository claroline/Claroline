import angular from 'angular/index'
import {} from 'angular-loading-bar'
import {} from 'ng-file-upload'
import {} from '#/main/core/innova/angular-translation'

import {} from '#/main/core/modal/module'
import {} from '#/main/core/api/router/module'
import {} from '#/main/core/scaffolding/asset/module'
import {} from '#/main/core/html-truster/module'
import Register from '#/main/core/utilities/register'
import resourceIconItemList from './resource-icon-item-list.directive'
import resourceIconItemService from './resource-icon-item-list.service'

let resourceIconItemListApp =  new Register(
  'resourceIconItemListApp',
  [
    'angular-loading-bar',
    'ngFileUpload',
    'ui.modal',
    'ui.html-truster',
    'ui.asset',
    'ui.bootstrap',
    'ui.fos-js-router',
    'ui.translation'
  ])

resourceIconItemListApp
  .directive('resourceIconItemList', resourceIconItemList)
  .service('resourceIconItemService', resourceIconItemService)
  .value('iconSet', window.iconSet)
  .value('iconItemsByTypes', window.iconItemsByTypes)

//Bootstrap angular in body
angular.element(document).ready(function () {
  angular.bootstrap(document.getElementsByTagName('body')[ 0 ], [ 'resourceIconItemListApp' ])
})