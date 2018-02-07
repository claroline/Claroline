import angular from 'angular/index'
import 'angular-toarrayfilter/toArrayFilter'
import 'angular-sanitize'
import 'angular-resource'
import 'angular-animate'
import 'angular-ui-tinymce'

import '../modules/translation'
import '../modules/urlInterpolator'
import '../modules/appDirectives/appDirectives'
import '../modules/tinymceConfig'

import CommentsController from './controllers/commentsController'
import PortfoliosController from './controllers/portfoliosController'
import CommentsDirective from './directives/commentsDirective'
import PortfoliosDirective from './directives/portfoliosDirective'
import CommentFactory from './services/commentFactory'
import CommentManager from './services/commentsManager'
import PortfolioFactory from './services/portfolioFactory'
import PortfolioManager from './services/portfolioManager'

var commentsApp = angular.module('commentsApp', ['ngResource', 'ngSanitize', 'ngAnimate', 'ui.tinymce',
  'app.translation', 'app.interpolator', 'app.directives', 'angular-toArrayFilter', 'app.config'])

commentsApp.value('assetPath', window.assetPath)

commentsApp.config(['$httpProvider', function ($http) {
  var elementToRemove = ['title', 'id', 'type', 'unreadComments']

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

commentsApp.value('assetPath', window.assetPath)
commentsApp.directive('commentContainer', CommentsDirective)
commentsApp.directive('portfolioContainer', PortfoliosDirective)
commentsApp.factory('commentFactory', ['$resource', CommentFactory])
commentsApp.factory('commentsManager', ['$http', 'commentFactory', 'urlInterpolator', CommentManager])
commentsApp.factory('portfolioFactory', ['$resource', PortfolioFactory])
commentsApp.factory('portfolioManager', ['$http', '$q', 'portfolioFactory', PortfolioManager])
commentsApp.controller('commentsController', ['$scope', '$timeout', 'commentsManager', 'assetPath', 'tinyMceConfig', CommentsController])
commentsApp.controller('portfoliosController', ['$scope', 'portfolioManager', 'commentsManager', '$filter', PortfoliosController])
