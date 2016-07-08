/* global Routing */

import angular from 'angular/index'

angular.module('ui.fos-js-router', [])
  .filter('path', () => generateUrl)
  .service('url', () => generateUrl)
  
function generateUrl(route, parameters = {}) {
  return Routing.generate(route, parameters)
}
