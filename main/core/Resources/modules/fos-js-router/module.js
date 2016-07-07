import angular from 'angular/index'
import 'angular/angular.min'

import RouterService from './Service/RouterService'

angular.module('ui.fos-js-router', [])
  .service('RouterService', RouterService)
