/**
 * Primary resources module
 */

import 'angular/index'
import 'angular-ui-resource-picker/angular-resource-picker'

import '../confirm/module'

import ResourcesPrimaryShowCtrl from './Controller/ResourcesPrimaryShowCtrl'
import ResourcesPrimaryEditCtrl from './Controller/ResourcesPrimaryEditCtrl'
import ResourcesPrimaryShowDirective from './Directive/ResourcesPrimaryShowDirective'
import ResourcesPrimaryEditDirective from './Directive/ResourcesPrimaryEditDirective'

angular
  .module('ResourcePrimary', [
    'ui.resourcePicker',
    'Confirm',
    'Resource'
  ])
  .controller('ResourcesPrimaryShowCtrl', [
    ResourcesPrimaryShowCtrl
  ])
  .controller('ResourcesPrimaryEditCtrl', [
    '$scope',
    'ConfirmService',
    'ResourceService',
    ResourcesPrimaryEditCtrl
  ])
  .directive('resourcesPrimaryShow', [
    () => new ResourcesPrimaryShowDirective
  ])
  .directive('resourcesPrimaryEdit', [
    () => new ResourcesPrimaryEditDirective
  ])
