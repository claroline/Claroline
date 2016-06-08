import angular from 'angular/index'
import {} from 'angular-route'
import {} from 'angular-animate'
import {} from 'angular-loading-bar'
import {} from '../fos-js-router/module'
import {} from '../asset/module'
import {} from '../html-truster/module'
import {} from 'angular-bootstrap'
import {} from 'angular-ui-translation/angular-translation'
import portalSearch from './portal-search/portal-search.directive'
import portalService from './portal.service'
import register from '../register/register'
import router from './routing'

let portalApp = new register(
  'portalApp',
  [
    'angular-loading-bar',
    'ngRoute',
    'ngAnimate',
    'ui.asset',
    'ui.bootstrap',
    'ui.fos-js-router',
    'ui.html-truster',
    'ui.translation'
  ]);

portalApp
  .config(router)
  .directive('portalSearch', portalSearch)
  .service('portalService', portalService)
  .filter('resourcePath', ['$filter', $filter => resource => {
    if (resource.resourceType == 'directory') {
      return $filter('path')('claro_workspace_open_tool', {'workspaceId': resource.workspaceId, 'toolName': 'home'})
    } else {
      return $filter('path')('claro_resource_open_short', {'node': resource.id})
    }
  }])

//Bootstrap angular in body
angular.element(document).ready(function () {
  angular.bootstrap(document.getElementsByTagName('body')[ 0 ], [ 'portalApp' ]);
})