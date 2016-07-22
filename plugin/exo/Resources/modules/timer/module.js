/**
 * Timer module
 */

import angular from 'angular/index'
import 'angular-ui-translation/angular-translation'
import 'ngstorage'

import TimerService from './Services/TimerService'
import TimerDirective from './Directives/TimerDirective'

angular
  .module('Timer', ['ui.translation', 'ngStorage'])
  .service('TimerService', ['$timeout', '$localStorage', TimerService])
  .directive('timer', ['TimerService', TimerDirective])
