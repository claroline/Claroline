import 'angular/angular.min'
import StickBottomDirective from './Directive/StickBottomDirective'

angular.module('ui.scrollbar', [])
  .directive(
      'scrollBottom',
       ['$parse', '$window', '$timeout', ($parse, $window, $timeout) => new StickBottomDirective($parse, $window, $timeout)]
   )
