import 'angular/angular.min'

import bootstrap from 'angular-bootstrap'
import translation from 'angular-ui-translation/angular-translation'
import bazinga from '../fos-js-router/module'

import PluginController from './Controller/PluginController'
import WarningController from './Controller/WarningController'
import PluginDirective from './Directive/PluginDirective'
import Interceptors from '../interceptorsDefault'

angular.module('PluginManager', [
  'ui.fos-js-router',
  'ui.bootstrap',
  'ui.bootstrap.tpls',
  'ui.translation',
])
  .controller('PluginController', ['$http', '$uibModal', PluginController])
  .controller('WarningController', WarningController)
  .directive('pluginManager', () => new PluginDirective)
  .config(Interceptors)
