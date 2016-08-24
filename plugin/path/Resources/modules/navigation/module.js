/**
 * Navigation module
 * Manages the breadcrumb of the Path
 */

import angular from 'angular/index'

import PathNavigationCtrl from './Controller/PathNavigationCtrl'
import PathNavigationDirective from './Directive/PathNavigationDirective'
import PathNavigationItemDirective from './Directive/PathNavigationItemDirective'

angular
  .module('Navigation', [])
  .controller('PathNavigationCtrl', [
    '$routeParams',
    '$scope',
    'PathService',
    PathNavigationCtrl
  ])
  .directive('pathNavigation', [
    () => new PathNavigationDirective
  ])
  .directive('pathNavigationItem', [
    () => new PathNavigationItemDirective
  ])
