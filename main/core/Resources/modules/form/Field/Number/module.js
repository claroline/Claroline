import angular from 'angular/index'
import '#/main/core/innova/angular-translation'

import NumberDirective from './NumberDirective'

angular.module('FieldNumber', ['ui.translation']).directive('formNumber', () => new NumberDirective)
