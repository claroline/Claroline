/**
 * Exercise Module
 */

import 'angular-bootstrap'
import 'angular-strap'
import 'angular-ui-tinymce'
import 'angular-ui-translation/angular-translation'
import '#/main/core/modal/module'

import './../common/module'
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
import FeedbackService from './Services/FeedbackService'

angular
    .module('Exercise', [
        'ui.translation',
        'ui.bootstrap',
        'ui.tinymce',
        'ui.modal',
        'mgcrea.ngStrap.datepicker',
        'Common',
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
        ExerciseService
    ])
    .service('FeedbackService', [
        FeedbackService
    ])
