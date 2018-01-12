import angular from 'angular/index'

import 'angular-ui-translation/angular-translation'
import CheckboxDirective from './CheckboxDirective'

angular.module('FieldCheckbox', ['ui.translation']).directive('formCheckbox', () => new CheckboxDirective)
