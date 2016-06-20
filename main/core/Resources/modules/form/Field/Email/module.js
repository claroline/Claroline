import 'angular/angular.min'
import 'angular-bootstrap'

import translation from 'angular-ui-translation/angular-translation'
import EmailDirective from './EmailDirective'
import HelpBlock from '../../HelpBlock/module'

angular.module('FieldEmail', [
  'ui.translation',
  'ui.bootstrap',
  'HelpBlock'
])
  .directive('formEmail', () => new EmailDirective)
