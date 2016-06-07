/**
 * Timer module
 */

import 'angular-ui-translation/angular-translation'
import 'ngstorage'

import TimerService from './Services/TimerService'
import TimerDirective from './Directives/TimerDirective'

angular
  .module('Timer', ['ui.translation', 'ngStorage'])
  .service('TimerService', ['$timeout', '$localStorage', TimerDirective])
  .directive('timer', ['TimerService', TimerDirective])
