/**
 * Secondary resources module
 */

import angular from 'angular/index'
import 'angular-ui-resource-picker/angular-resource-picker'

import './../confirm/module'
import './../resource/module'

import ResourcesSecondaryShowCtrl from './Controller/ResourcesSecondaryShowCtrl'
import ResourcesSecondaryEditCtrl from './Controller/ResourcesSecondaryEditCtrl'
import ResourcesSecondaryShowDirective from './Directive/ResourcesSecondaryShowDirective'
import ResourcesSecondaryEditDirective from './Directive/ResourcesSecondaryEditDirective'

angular
  .module('ResourceSecondary', [
    'ui.resourcePicker',
    'Confirm',
    'Resource'
  ])
  .controller('ResourcesSecondaryShowCtrl', [
    ResourcesSecondaryShowCtrl
  ])
  .controller('ResourcesSecondaryEditCtrl', [
    '$scope',
    'ConfirmService',
    'ResourceService',
    ResourcesSecondaryEditCtrl
  ])
  .directive('resourcesSecondaryShow', [
    () => new ResourcesSecondaryShowDirective
  ])
  .directive('resourcesSecondaryEdit', [
    () => new ResourcesSecondaryEditDirective
  ])