/**
 * Path module
 */

import angular from 'angular/index'
import 'angular-ui-tinymce/src/tinymce'

import '#/main/core/fos-js-router/module'
import '#/main/core/translation/module'
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
    'ui.fos-js-router',
    'translation',
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
    'Translator',
    'url',
    'AlertService',
    'StepService',
    'UserProgressionService',
    PathService
  ])
  .controller('PathShowCtrl', [
    '$window',
    '$route',
    '$routeParams',
    'url',
    'PathService',
    'AuthorizationCheckerService',
    'UserProgressionService',
    PathShowCtrl
  ])
  .controller('PathEditCtrl', [
    '$window',
    '$route',
    '$routeParams',
    'url',
    'PathService',
    'Translator',
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