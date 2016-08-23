import angular from 'angular/index'
import StickBottomDirective from './Directive/StickBottomDirective'

angular.module('ui.scrollbar', [])
  .directive(
    'scrollBottom',
    ['$parse', '$window', '$timeout', ($parse, $window, $timeout) => new StickBottomDirective($parse, $window, $timeout)]
)
