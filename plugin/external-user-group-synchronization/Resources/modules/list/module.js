import angular from 'angular/index'
import {} from '#/main/core/modal/module'
import {} from 'angular-bootstrap'
import {} from 'angular-loading-bar'
import {} from 'angular-ui-translation/angular-translation'
import {} from '#/main/core/fos-js-router/module'
import {} from '#/main/core/asset/module'
import {} from '#/main/core/html-truster/module'
import register from '#/main/core/register/register'
import externalSourceList from './list.directive'
import externalSourceListService from './list.service'

let externalSourceListApp =  new register(
  'externalSourceListApp',
  [
    'angular-loading-bar',
    'ui.modal',
    'ui.html-truster',
    'ui.asset',
    'ui.bootstrap',
    'ui.fos-js-router',
    'ui.translation'
  ])

externalSourceListApp
  .directive('externalSourceList', externalSourceList)
  .service('externalSourceListService', externalSourceListService)
  .value('externalSources', window.externalSources)

//Bootstrap angular in body
angular.element(document).ready(function () {
  angular.bootstrap(document.getElementsByTagName('body')[ 0 ], [ 'externalSourceListApp' ])
})