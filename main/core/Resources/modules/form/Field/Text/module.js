import angular from 'angular/index'
import 'angular-ui-translation/angular-translation'

import TextDirective from './TextDirective'
import '../../HelpBlock/module'

angular.module('FieldText', ['ui.translation', 'HelpBlock'])
  .directive('formText', ['$parse', '$compile', ($parse, $compile) => new TextDirective($parse, $compile)])
