import angular from 'angular/index'
import 'angular-bootstrap'

import 'angular-ui-translation/angular-translation'
import EmailDirective from './EmailDirective'
import '../../HelpBlock/module'

angular.module('FieldEmail', [
  'ui.translation',
  'ui.bootstrap',
  'HelpBlock'
])
  .directive('formEmail', () => new EmailDirective)
