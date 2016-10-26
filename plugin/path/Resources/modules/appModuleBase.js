/**
 * Base module for Path component
 */

import angular from 'angular/index'
import 'angular-loading-bar'

angular
  .module('PathModuleBase', [
    'angular-loading-bar'
  ])
  // Common Path configuration
  .config([
    'cfpLoadingBarProvider',
    (cfpLoadingBarProvider) => {
      // Configure loader
      cfpLoadingBarProvider.latencyThreshold = 200
      cfpLoadingBarProvider.includeBar       = false
      cfpLoadingBarProvider.spinnerTemplate  = '<div class="loading">Loading&#8230;</div>'
    }])
