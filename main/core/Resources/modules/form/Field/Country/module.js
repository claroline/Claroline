import angular from 'angular/index'

import '#/main/core/innova/angular-translation'
import CountryDirective from './CountryDirective'

angular.module('FieldCountry', ['ui.translation']).directive('formCountry', () => new CountryDirective)
