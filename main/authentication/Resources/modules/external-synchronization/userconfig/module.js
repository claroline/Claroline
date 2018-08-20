import angular from 'angular/index'
import {} from '#/main/core/modal/module'
import {} from 'angular-loading-bar'
import {} from '#/main/core/innova/angular-translation'
import {} from '#/main/core/api/router/module'
import {} from '#/main/core/scaffolding/asset/module'
import {} from '#/main/core/html-truster/module'
import Register from '#/main/core/utilities/register'
import externalSourceUserConfig from './userconfig.directive'
import externalSourceUserConfigService from './userconfig.service'

let externalSourceUserConfigApp =  new Register(
  'externalSourceUserConfigApp',
  [
    'angular-loading-bar',
    'ui.modal',
    'ui.html-truster',
    'ui.asset',
    'ui.bootstrap',
    'ui.fos-js-router',
    'ui.translation'
  ])

externalSourceUserConfigApp
    .directive('externalSourceUserConfig', externalSourceUserConfig)
    .service('externalSourceUserConfigService', externalSourceUserConfigService)
    .value('externalSource', window.externalSource)
    .value('sourceConfig', window.sourceConfig)
    .value('tableNames', window.tableNames)
    .value('fieldNames', window.fieldNames)


//Bootstrap angular in body
angular.element(document).ready(function () {
  angular.bootstrap(document.getElementsByTagName('body')[ 0 ], [ 'externalSourceUserConfigApp' ])
})