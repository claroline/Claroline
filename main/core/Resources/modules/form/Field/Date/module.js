import 'angular/angular.min'
import 'angular-bootstrap'

import translation from 'angular-ui-translation/angular-translation'
import DateDirective from './DateDirective'

angular.module('FieldDate', [
  'ui.translation',
  'ui.bootstrap'
])
  .directive('formDate', () => new DateDirective)
