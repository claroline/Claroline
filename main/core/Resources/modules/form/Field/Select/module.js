import 'angular/angular.min'

import translation from 'angular-ui-translation/angular-translation'
import SelectDirective from './SelectDirective'

angular.module('FieldSelect', ['ui.translation']).directive('formSelect', () => new SelectDirective)
