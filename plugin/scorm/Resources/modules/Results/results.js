/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import angular from 'angular/index'

import 'angular-ui-router'
import 'angular-ui-bootstrap'
import 'angular-animate'
import '#/main/core/innova/angular-translation'
import 'angular-loading-bar'
import 'ng-table'

import ScormResultsCtrl from './Controller/ScormResultsCtrl'
import ScormResultsService from './Service/ScormResultsService'
import ScormResultsDirective from './Directive/ScormResultsDirective'

angular.module('ScormResultsModule', [
  'ui.router',
  'ui.translation',
  'ui.bootstrap',
  'ui.bootstrap.tpls',
  'ngAnimate',
  'angular-loading-bar',
  'ngTable'
])
.controller('ScormResultsCtrl', ['NgTableParams', 'ScormResultsService', ScormResultsCtrl])
.service('ScormResultsService', ScormResultsService)
.directive('scormResults', () => new ScormResultsDirective)
.config([
  'cfpLoadingBarProvider',
  function configureLoadingBar(cfpLoadingBarProvider) {
    // Configure loader
    cfpLoadingBarProvider.latencyThreshold = 200
    cfpLoadingBarProvider.includeBar = true
    cfpLoadingBarProvider.includeSpinner = true
    //cfpLoadingBarProvider.spinnerTemplate = '<div class="loading">Loading&#8230;</div>';
  }
])