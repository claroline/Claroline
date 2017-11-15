import angular from 'angular/index'
import Controller from './indexController'
import Directive from './indexDirective'

import '../comments/commentsApp'
import '../widget/widgetsApp'
import '../statistics/statisticsApp'
import '../widget/widgetsApp'
import '../utils/Array'
import '#/main/core/html-truster/module'

var indexApp = angular.module('indexApp', ['commentsApp', 'widgetsApp', 'statisticsApp', 'ui.html-truster'])
indexApp.value('assetPath', window.assetPath)
indexApp.controller('indexController', ['$scope', 'widgetManager', 'assetPath', Controller])
indexApp.directive('indexContainer', Directive)


// Bootstrap portfolio application
angular.element(document).ready(function () {
  angular.bootstrap('#portfolio-index-module', ['indexApp'])
})