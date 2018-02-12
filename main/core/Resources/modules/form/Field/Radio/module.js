import angular from 'angular/index'
import '#/main/core/innova/angular-translation'

import RadioDirective from './RadioDirective'

angular.module('FieldRadio', ['ui.translation']).directive('formRadio', () => new RadioDirective)
