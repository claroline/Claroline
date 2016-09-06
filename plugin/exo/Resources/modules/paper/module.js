/**
 * Paper module
 */

import angular from 'angular/index'

import 'angular-bootstrap'
import 'angular-ui-translation/angular-translation'
import 'at-table/dist/angular-table'

import './../common/module'

import PaperService from './Services/PaperService'
import UserPaperService from './Services/UserPaperService'
import ManualMarkCtrl from './Controllers/ManualMarkCtrl'
import PaperListCtrl from './Controllers/PaperListCtrl'
import PaperShowCtrl from './Controllers/PaperShowCtrl'
import '#/main/core/fos-js-router/module'

angular
  .module('Paper', [
    'ui.translation',
    'ui.bootstrap',
    'angular-table',
    'ui.fos-js-router',
    'Common'
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
    'PaperService',
    'ExerciseService',
    'url',
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
