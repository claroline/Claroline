import angular from 'angular/index'

import {generateUrl} from './index'

angular
  .module('ui.fos-js-router', [])
  .filter('path', () => generateUrl)
  .service('url', () => generateUrl)
