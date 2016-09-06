/**
* Exercise Module
*/

import angular from 'angular/index'

import 'angular-bootstrap'
import 'angular-strap'
import 'angular-ui-tinymce'
import 'angular-ui-translation/angular-translation'
import '#/main/core/modal/module'
import '#/main/core/fos-js-router/module'
import '#/main/core/translation/module'

import './../common/module'
import './../feedback/module'
import './../step/module'
import './../paper/module'
import './../timer/module'

import ExerciseCtrl from './Controllers/ExerciseCtrl'
import ExerciseMetadataCtrl from './Controllers/ExerciseMetadataCtrl'
import ExerciseOverviewCtrl from './Controllers/ExerciseOverviewCtrl'
import ExercisePlayerCtrl from './Controllers/ExercisePlayerCtrl'
import ExerciseDirective from './Directives/ExerciseDirective'
import StartButtonDirective from './Directives/StartButtonDirective'
import ExerciseService from './Services/ExerciseService'

angular
  .module('Exercise', [
    'ui.translation',
    'ui.bootstrap',
    'ui.tinymce',
    'ui.modal',
    'mgcrea.ngStrap.datepicker',
    'translation',
    'Common',
    'Feedback',
    'Step',
    'Paper',
    'Timer'
  ])
  .controller('ExerciseCtrl', [
    'ExerciseService',
    'PaperService',
    'UserPaperService',
    '$route',
    ExerciseCtrl
  ])
  .controller('ExerciseMetadataCtrl', [
    '$location',
    'ExerciseService',
    'TinyMceService',
    ExerciseMetadataCtrl
  ])
  .controller('ExerciseOverviewCtrl', [
    'ExerciseService',
    'UserPaperService',
    ExerciseOverviewCtrl
  ])
  .controller('ExercisePlayerCtrl', [
    '$location',
    'step',
    'paper',
    'ExerciseService',
    'FeedbackService',
    'UserPaperService',
    'TimerService',
    ExercisePlayerCtrl
  ])
  .directive('exercise', [
    ExerciseDirective
  ])
  .directive('buttonStart', [
    StartButtonDirective
  ])
  .service('ExerciseService', [
    '$http',
    '$q',
    'Translator',
    'url',
    ExerciseService
  ])
