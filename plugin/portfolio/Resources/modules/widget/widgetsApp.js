import WidgetController from './controllers/widgetController'
import WidgetDirective from './directives/widgetDirective'
import WidgetFactory from './services/widgetFactory'
import WidgetManager from './services/widgetManager'

import angular from 'angular/index'
import 'angular-resource'
import 'angular-sanitize'
import 'angular-animate'
import 'angular-ui-tinymce'
import 'angular-datetime-input'

import '../modules/translation'
import '../modules/urlInterpolator'
import '../modules/tinymceConfig'
import '../modules/appDirectives/appDirectives'
import '../modules/datepickerDirective'
import '../modules/badgePickerDirective'

import 'angular-strap'
import '#/main/core/innova/angular-resource-picker'
import 'angular-ui-bootstrap'

var widgetsApp = angular.module('widgetsApp', ['ngResource', 'ngSanitize', 'ngAnimate', 'ui.tinymce',
  'ui.resourcePicker', 'ui.badgePicker', 'ui.datepicker', 'datetime', 'mgcrea.ngStrap.popover',
  'ui.bootstrap.collapse', 'app.translation', 'app.interpolator', 'app.directives', 'app.config'])

widgetsApp.config(['$httpProvider', function ($http) {
  var elementToRemove = ['views', 'isCollapsed', 'isEditing', 'isUpdating', 'isDeleting', 'isNew']

  $http.defaults.transformRequest.push(function (data) {
    data = angular.fromJson(data)
    angular.forEach(data, function (element, index) {
      if(elementToRemove.inArray(index)) {
        delete data[index]
      }
    })
    return JSON.stringify(data)
  })
}])
widgetsApp.value('assetPath', window.assetPath)

widgetsApp.directive('widgetContainer', WidgetDirective)
widgetsApp.factory('widgetFactory', ['$resource', 'urlInterpolator', WidgetFactory])
widgetsApp.factory('widgetManager', ['$http', '$q', 'widgetFactory', '$filter', WidgetManager])
widgetsApp.controller('widgetController', ['$scope', 'widgetManager', '$attrs', 'tinyMceConfig', WidgetController])
