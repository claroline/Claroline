import angular from 'angular/index'
import 'angular-toArrayFilter/toArrayFilter'

//this hack is required for the daterange picker to work
import moment from 'moment'
window.moment = moment

import '../comments/commentsApp'
import '../modules/urlInterpolator'
import '../modules/translation'

import Directive from './directives/statisticsViewDirective'
import Controller from './controllers/statisticsViewController'

var statisticsApp = angular.module('statisticsApp', ['angular-toArrayFilter', 'daterangepicker'])

statisticsApp.controller('statisticsViewController', ['$scope', 'portfolioManager', '$filter', '$http', 'urlInterpolator', 'translationService', Controller])
statisticsApp.directive('statisticsViewContainer', Directive)
