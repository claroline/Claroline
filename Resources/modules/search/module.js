import 'angular/angular.min'
import 'angular-ui-select'

import Interceptors from '../interceptorsDefault'
import ClarolineSearchController from './Controller/ClarolineSearchController'
import ClarolineSearchDirective from './Directive/ClarolineSearchDirective'
import ClarolineSearchService from './Service/ClarolineSearchService'
import SearchOptionsService  from './Service/SearchOptionsService'
import HtmlTruster from '../html-truster/module'

angular.module('ClarolineSearch', ['ui.select', 'ui.html-truster'])
	.config(Interceptors)
	.directive('clarolinesearch', () => new ClarolineSearchDirective)
	.service('SearchOptionsService', () => new SearchOptionsService)
	.provider('ClarolineSearchService', () => new ClarolineSearchService)
