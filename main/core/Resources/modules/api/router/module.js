/* global Routing */

import angular from 'angular/index'

function generateUrl(route, parameters = {}, absolute = false) {
  return Routing.generate(route, parameters, absolute)
}

angular
  .module('ui.fos-js-router', [])
  .filter('path', () => generateUrl)
  .service('url', () => generateUrl)
