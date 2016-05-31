import 'angular/angular.min'

import translation from 'angular-ui-translation/angular-translation'
import CheckboxDirective from './CheckboxDirective'

angular.module('FieldCheckbox', ['ui.translation']).directive('formCheckbox', () => new CheckboxDirective)
