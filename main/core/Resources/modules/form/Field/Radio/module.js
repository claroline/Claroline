import 'angular/angular.min'

import translation from 'angular-ui-translation/angular-translation'
import RadioDirective from './RadioDirective'

angular.module('FieldRadio', ['ui.translation']).directive('formRadio', () => new RadioDirective)
