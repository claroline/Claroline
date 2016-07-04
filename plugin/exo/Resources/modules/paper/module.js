/**
 * Paper module
 */

import 'angular-bootstrap'
import 'angular-ui-translation/angular-translation'
import 'at-table/dist/angular-table'

import './../common/module'

import PaperService from './Services/PaperService'
import UserPaperService from './Services/UserPaperService'
import ManualMarkCtrl from './Controllers/ManualMarkCtrl'
import PaperListCtrl from './Controllers/PaperListCtrl'
import PaperShowCtrl from './Controllers/PaperShowCtrl'

angular
  .module('Paper', [
    'ui.translation',
    'ui.bootstrap',
    'angular-table',
    'Common'
  ])
  .service('PaperService', [
    '$http',
    '$q',
    'ExerciseService',
    'StepService',
    'QuestionService',
    PaperService
  ])
  .service('UserPaperService', [
    '$http',
    '$q',
    'PaperService',
    'ExerciseService',
    UserPaperService
  ])
  .controller('ManualMarkCtrl', [
    '$uibModalInstance',
    'PaperService',
    'question',
    ManualMarkCtrl
  ])
  .controller('PaperListCtrl', [
    '$filter',
    'CommonService',
    'ExerciseService',
    'PaperService',
    'UserPaperService',
    'papers',
    PaperListCtrl
  ])
  .controller('PaperShowCtrl', [
    'paperPromise',
    'PaperService',
    PaperShowCtrl
  ])
