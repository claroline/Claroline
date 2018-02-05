/**
 * Primary resources module
 */

import angular from 'angular/index'
import '#/main/core/innova/angular-resource-picker'

import '#/main/core/api/router/module'
import '#/main/core/translation/module'
import '../confirm/module'
import '../resource/module'

import ResourcesPrimaryShowCtrl from './Controller/ResourcesPrimaryShowCtrl'
import ResourcesPrimaryEditCtrl from './Controller/ResourcesPrimaryEditCtrl'
import ResourcesPrimaryShowDirective from './Directive/ResourcesPrimaryShowDirective'
import ResourcesPrimaryEditDirective from './Directive/ResourcesPrimaryEditDirective'

angular
  .module('ResourcePrimary', [
    'ui.resourcePicker',
    'ui.fos-js-router',
    'translation',
    'Confirm',
    'Resource'
  ])
  .controller('ResourcesPrimaryShowCtrl', [
    'url',
    'ResourceService',
    ResourcesPrimaryShowCtrl
  ])
  .controller('ResourcesPrimaryEditCtrl', [
    'url',
    'ResourceService',
    '$scope',
    'Translator',
    'ConfirmService',
    ResourcesPrimaryEditCtrl
  ])
  .directive('resourcesPrimaryShow', [
    () => new ResourcesPrimaryShowDirective
  ])
  .directive('resourcesPrimaryEdit', [
    () => new ResourcesPrimaryEditDirective
  ])
