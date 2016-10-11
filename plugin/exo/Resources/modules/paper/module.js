/**
 * Paper module
 */

import angular from 'angular/index'

import 'angular-bootstrap'
import 'angular-ui-translation/angular-translation'
import 'at-table/dist/angular-table'

import '#/main/core/utilities/module'
import './../common/module'
import './../correction/module'

import PaperService from './Services/PaperService'
import PaperGenerator from './Services/PaperGenerator'
import UserPaperService from './Services/UserPaperService'
import ManualMarkCtrl from './Controllers/ManualMarkCtrl'
import PaperListCtrl from './Controllers/PaperListCtrl'
import PaperShowCtrl from './Controllers/PaperShowCtrl'
import '#/main/core/fos-js-router/module'

angular
  .module('Paper', [
    'utilities',
    'ui.translation',
    'ui.bootstrap',
    'angular-table',
    'ui.fos-js-router',
    'Common',
    'Step',
    'Correction'
  ])
  .service('PaperGenerator', [
    '$filter',
    'IdentifierService',
    'ArrayService',
    PaperGenerator
  ])
  .service('PaperService', [
    '$http',
    '$q',
    'ExerciseService',
    'StepService',
    'QuestionService',
    'url',
    PaperService
  ])
  .service('UserPaperService', [
    '$http',
    '$q',
    '$filter',
    'PaperGenerator',
    'PaperService',
    'ExerciseService',
    'url',
    'CorrectionMode',
    'MarkMode',
    UserPaperService
  ])
  .controller('ManualMarkCtrl', [
    '$uibModalInstance',
    'PaperService',
    'QuestionService',
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
    'attempt',
    'PaperService',
    'UserPaperService',
    PaperShowCtrl
  ])
