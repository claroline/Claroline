import 'angular/angular.min'

import translation from 'angular-ui-translation/angular-translation'
import TextDirective from './TextDirective'
import HelpBlock from '../../HelpBlock/module'

angular.module('FieldText', ['ui.translation', 'HelpBlock'])
  .directive('formText', ['$parse', '$compile', ($parse, $compile) => new TextDirective($parse, $compile)])
