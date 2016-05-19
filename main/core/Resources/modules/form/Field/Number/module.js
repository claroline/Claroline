import 'angular/angular.min'

import translation from 'angular-ui-translation/angular-translation'
import NumberDirective from './NumberDirective'

angular.module('FieldNumber', ['ui.translation']).directive('formNumber', () => new NumberDirective)
