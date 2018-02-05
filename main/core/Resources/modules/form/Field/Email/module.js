import angular from 'angular/index'
import 'angular-ui-bootstrap'

import '#/main/core/innova/angular-translation'
import EmailDirective from './EmailDirective'
import '../../HelpBlock/module'

angular.module('FieldEmail', [
  'ui.translation',
  'ui.bootstrap',
  'HelpBlock'
])
  .directive('formEmail', () => new EmailDirective)
