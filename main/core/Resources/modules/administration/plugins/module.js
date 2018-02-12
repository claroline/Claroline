import angular from 'angular/index'
import 'angular-ui-bootstrap'
import '#/main/core/innova/angular-translation'

import '#/main/core/api/router/module'

import PluginController from './Controller/PluginController'
import WarningController from './Controller/WarningController'
import PluginDirective from './Directive/PluginDirective'
import Interceptors from '#/main/core/interceptorsDefault'

angular.module('PluginManager', [
  'ui.fos-js-router',
  'ui.bootstrap',
  'ui.bootstrap.tpls',
  'ui.translation'
])
  .controller('PluginController', ['$http', '$uibModal', PluginController])
  .controller('WarningController', WarningController)
  .directive('pluginManager', () => new PluginDirective)
  .config(Interceptors)
