import angular from 'angular/index'

import 'angular-ui-translation/angular-translation'
import CountryDirective from './CountryDirective'

angular.module('FieldCountry', ['ui.translation']).directive('formCountry', () => new CountryDirective)
