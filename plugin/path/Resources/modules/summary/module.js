/**
 * Summary module
 */

import angular from 'angular/index'
import 'angular-bootstrap'
import 'angular-ui-tree/dist/angular-ui-tree.min'

import '#/main/core/translation/module'
import '../utils/module'
import '../clipboard/module'
import '../confirm/module'
import '../user-progression/module'

import SummaryService from './Service/SummaryService'
import SummaryShowCtrl from './Controller/SummaryShowCtrl'
import SummaryEditCtrl from './Controller/SummaryEditCtrl'
import SummaryShowDirective from './Directive/SummaryShowDirective'
import SummaryEditDirective from './Directive/SummaryEditDirective'
import SummaryItemShowDirective from './Directive/SummaryItemShowDirective'
import SummaryItemEditDirective from './Directive/SummaryItemEditDirective'

angular
  .module('Summary', [
    'ui.bootstrap',
    'ui.tree',
    'translation',
    'Utils',
    'Clipboard',
    'Confirm',
    'UserProgression'
  ])
  .service('SummaryService', [
    SummaryService
  ])
  .controller('SummaryShowCtrl', [
    'SummaryService',
    'PathService',
    'UserProgressionService',
    SummaryShowCtrl
  ]).
  controller('SummaryEditCtrl', [
    'SummaryService',
    'PathService',
    SummaryEditCtrl
  ])
  .directive('pathSummaryShow', [
    () => new SummaryShowDirective
  ])
  .directive('pathSummaryEdit', [
    () => new SummaryEditDirective
  ])
  .directive('summaryItemShow', [
    () => new SummaryItemShowDirective
  ])
  .directive('summaryItemEdit', [
    () => new SummaryItemEditDirective
  ])
