/**
 * Path module
 */

import angular from 'angular/index'
import 'angular-ui-tinymce/src/tinymce'

import './../history/module'
import './../clipboard/module'
import './../authorization/module'
import './../summary/module'
import './../navigation/module'
import './../step/module'
import './../user-progression/module'
import './../utils/module'

import PathService from './Service/PathService'
import PathShowCtrl from './Controller/PathShowCtrl'
import PathEditCtrl from './Controller/PathEditCtrl'
import PathShowDirective from './Directive/PathShowDirective'
import PathEditDirective from './Directive/PathEditDirective'

angular
  .module('Path', [
    'ui.tinymce',
    'Utils',
    'History',
    'Clipboard',
    'Authorization',
    'Navigation',
    'Summary',
    'Step',
    'UserProgression'
  ])
  .service('PathService', [
    '$http',
    '$q',
    '$timeout',
    '$location',
    'AlertService',
    'StepService',
    'UserProgressionService',
    PathService
  ])
  .controller('PathShowCtrl', [
    '$window',
    '$route',
    '$routeParams',
    'PathService',
    'AuthorizationCheckerService',
    'UserProgressionService',
    PathShowCtrl
  ])
  .controller('PathEditCtrl', [
    '$window',
    '$route',
    '$routeParams',
    'PathService',
    'HistoryService',
    'ConfirmService',
    '$scope',
    'tinymceConfig',
    PathEditCtrl
  ])
  .directive('pathShow', [
    () => new PathShowDirective
  ])
  .directive('pathEdit', [
    () => new PathEditDirective
  ])