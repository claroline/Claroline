/**
 * Secondary resources module
 */

import angular from 'angular/index'
import '#/main/core/innova/angular-resource-picker'

import '#/main/core/translation/module'
import './../confirm/module'
import './../resource/module'

import ResourcesSecondaryShowCtrl from './Controller/ResourcesSecondaryShowCtrl'
import ResourcesSecondaryEditCtrl from './Controller/ResourcesSecondaryEditCtrl'
import ResourcesSecondaryShowDirective from './Directive/ResourcesSecondaryShowDirective'
import ResourcesSecondaryEditDirective from './Directive/ResourcesSecondaryEditDirective'

angular
  .module('ResourceSecondary', [
    'ui.resourcePicker',
    'translation',
    'Confirm',
    'Resource'
  ])
  .controller('ResourcesSecondaryShowCtrl', [
    'url',
    'ResourceService',
    ResourcesSecondaryShowCtrl
  ])
  .controller('ResourcesSecondaryEditCtrl', [
    'url',
    'ResourceService',
    '$scope',
    'Translator',
    'ConfirmService',
    ResourcesSecondaryEditCtrl
  ])
  .directive('resourcesSecondaryShow', [
    () => new ResourcesSecondaryShowDirective
  ])
  .directive('resourcesSecondaryEdit', [
    () => new ResourcesSecondaryEditDirective
  ])
