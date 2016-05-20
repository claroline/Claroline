import 'angular/angular.min'

import translation from 'angular-ui-translation/angular-translation'
import CountryDirective from './CountryDirective'

angular.module('FieldCountry', ['ui.translation']).directive('formCountry', () => new CountryDirective)
