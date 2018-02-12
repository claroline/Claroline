import CommentController from './controllers/commentController'
import PortfolioController from './controllers/portfolioController'
import WidgetController from './controllers/widgetController'
import WidgetPickerController from './controllers/widgetPickerController'

import CommentsDirective from './directives/commentsDirective'
import PortfolioDirective from './directives/portfolioDirective'
import WidgetDirective from './directives/widgetDirective'

import CommentFactory from './services/commentFactory'
import CommentsManager from './services/commentsManager'
import PortfolioFactory from './services/portfolioFactory'
import PortfolioManager from './services/portfolioManager'
import WidgetFactory from './services/widgetFactory'
import WidgetConfig from './services/widgetsConfig'
import WidgetManager from './services/widgetsManager'

import angular from 'angular/index'
import 'angular-resource'
import 'angular-sanitize'
import 'angular-ui-tinymce'

import '../modules/translation'
import '../modules/urlInterpolator'
import '../modules/appDirectives/appDirectives'

import 'angular-strap'
import '#/main/core/innova/angular-resource-picker'
import 'angular-ui-bootstrap'
import 'confirm-bootstrap/confirm-bootstrap.js'

import {} from 'checklist-model'
import {} from 'lodash'
import '#/main/core/html-truster/module'

import '../utils/Array'

var portfolioApp = angular.module('portfolioApp', ['ngResource', 'ngSanitize', 'ui.tinymce',
  'mgcrea.ngStrap.popover', 'app.translation', 'app.interpolator', 'app.directives', 'gridster',
  'ui.bootstrap', 'checklist-model', 'ui.html-truster'])

portfolioApp.config(['$httpProvider', function ($http) {
  var elementToRemove = ['views', 'isUpdating', 'isDeleting', 'isEditing', 'isCollapsed', 'id', 'unreadComments', 'toSave', 'isNew', 'widget']

  $http.defaults.transformRequest.push(function (data) {
    data = angular.fromJson(data)
    angular.forEach(data, function (element, index) {
      if (elementToRemove.inArray(index)) {
        delete data[index]
      }
    })
    return JSON.stringify(data)
  })
}])

portfolioApp.value('assetPath', window.assetPath)

portfolioApp.factory('commentFactory', ['$resource', CommentFactory])
portfolioApp.factory('commentsManager', ['commentFactory', CommentsManager])
portfolioApp.factory('portfolioFactory', ['$resource', PortfolioFactory])
portfolioApp.factory('portfolioManager', ['portfolioFactory', 'widgetsManager', 'commentsManager', PortfolioManager])
portfolioApp.factory('widgetFactory', ['$resource', 'urlInterpolator', WidgetFactory])
portfolioApp.factory('widgetsConfig', [WidgetConfig])
portfolioApp.factory('widgetsManager', ['$http', 'widgetsConfig', 'widgetFactory', '$q', 'urlInterpolator', WidgetManager])

portfolioApp.controller('commentController', ['$scope', 'portfolioManager', 'commentsManager', '$timeout', CommentController])
portfolioApp.controller('portfolioController', ['$scope', 'portfolioManager', 'widgetsManager', 'commentsManager', '$attrs', 'widgetsConfig', 'assetPath', '$uibModal', '$timeout', PortfolioController])
portfolioApp.controller('widgetController', ['$scope', 'widgetsManager', '$uibModal', '$timeout', WidgetController])
portfolioApp.controller('widgetPickerController', ['$scope', '$uibModalInstance', 'portfolioWidgets', 'selectedPortfolioWidget', WidgetPickerController])

portfolioApp.directive('commentsContainer', CommentsDirective)
portfolioApp.directive('portfolioContainer', PortfolioDirective)
portfolioApp.directive('widget', WidgetDirective)


// Bootstrap portfolio application
angular.element(document).ready(function () {
  angular.bootstrap(document, ['portfolioApp'])
})