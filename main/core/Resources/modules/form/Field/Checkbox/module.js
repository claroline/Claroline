import angular from 'angular/index'

import '#/main/core/innova/angular-translation'
import CheckboxDirective from './CheckboxDirective'

angular.module('FieldCheckbox', ['ui.translation']).directive('formCheckbox', () => new CheckboxDirective)
