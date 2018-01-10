import angular from 'angular/index'
import 'angular-ui-bootstrap'
import 'angular-ui-translation/angular-translation'

import DateDirective from './DateDirective'

angular.module('FieldDate', [
  'ui.translation',
  'ui.bootstrap'
])
  .directive('formDate', () => new DateDirective)
