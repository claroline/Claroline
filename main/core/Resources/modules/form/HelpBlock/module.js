import angular from 'angular/index'
import 'angular-ui-translation/angular-translation'

import HelpBlockDirective from './HelpBlockDirective'

angular.module('HelpBlock', ['ui.translation'])
  .directive('helpBlock', ['$parse', '$compile', ($parse, $compile) => new HelpBlockDirective($parse, $compile)])
