import angular from 'angular/index'
import 'angular-ui-bootstrap'
import '#/main/core/innova/angular-translation'

import DateDirective from './DateDirective'

angular.module('FieldDate', [
  'ui.translation',
  'ui.bootstrap'
])
  .directive('formDate', () => new DateDirective)
