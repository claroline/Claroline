/**
 * Step module
 */

import angular from 'angular/index'
import '#/main/core/innova/angular-resource-picker'
import 'angular-ui-tinymce/src/tinymce'

import '#/main/core/api/router/module'
import '#/main/core/translation/module'
import '../utils/module'
import '../form/module'
import '../authorization/module'
import '../resource/module'
import '../resource-primary/module'
import '../resource-secondary/module'
import '../user-progression/module'
import '../condition/module'

import StepService from './Service/StepService'
import StepShowCtrl from './Controller/StepShowCtrl'
import StepEditCtrl from './Controller/StepEditCtrl'

angular
  .module('Step', [
    'ui.tinymce',
    'ui.resourcePicker',
    'ui.fos-js-router',
    'translation',
    'Utils',
    'Form',
    'Authorization',
    'Resource',
    'ResourcePrimary',
    'ResourceSecondary',
    'UserProgression',
    'Condition'
  ])
  .service('StepService', [
    '$http',
    '$filter',
    'Translator',
    'url',
    'IdentifierService',
    'ResourceService',
    StepService
  ])
  .controller('StepShowCtrl', [
    'step',
    'inheritedResources',
    'PathService',
    'SummaryService',
    'authorization',
    '$sce',
    'UserProgressionService',
    StepShowCtrl
  ])
  .controller('StepEditCtrl', [
    'step',
    'inheritedResources',
    'PathService',
    'SummaryService',
    'url',
    '$scope',
    'StepService',
    'tinymceConfig',
    StepEditCtrl
  ])