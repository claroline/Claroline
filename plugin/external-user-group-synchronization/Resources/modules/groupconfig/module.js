import angular from 'angular/index'
import {} from '#/main/core/modal/module'
import {} from 'angular-loading-bar'
import {} from '#/main/core/innova/angular-translation'
import {} from '#/main/core/api/router/module'
import {} from '#/main/core/scaffolding/asset/module'
import {} from '#/main/core/html-truster/module'
import register from '#/main/core/register/register'
import externalSourceGroupConfig from './groupconfig.directive'
import externalSourceGroupConfigService from './groupconfig.service'

let externalSourceGroupConfigApp =  new register(
  'externalSourceGroupConfigApp',
  [
    'angular-loading-bar',
    'ui.modal',
    'ui.html-truster',
    'ui.asset',
    'ui.bootstrap',
    'ui.fos-js-router',
    'ui.translation'
  ])

externalSourceGroupConfigApp
    .directive('externalSourceGroupConfig', externalSourceGroupConfig)
    .service('externalSourceGroupConfigService', externalSourceGroupConfigService)
    .value('externalSource', window.externalSource)
    .value('sourceConfig', window.sourceConfig)
    .value('tableNames', window.tableNames)
    .value('groupFieldNames', window.groupFieldNames)
    .value('userGroupFieldNames', window.userGroupFieldNames)


//Bootstrap angular in body
angular.element(document).ready(function () {
  angular.bootstrap(document.getElementsByTagName('body')[ 0 ], [ 'externalSourceGroupConfigApp' ])
})