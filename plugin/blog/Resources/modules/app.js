import angular from 'angular/index'
import 'angular-ui-translation/angular-translation'
import 'angular-bootstrap'
import 'angular-ui-calendar'
import 'angular-route'
import 'angular-ui-tinymce'
import 'angular-resource'
import 'angular-loading-bar'
import 'ng-file-upload'
import 'ng-tags-input/ng-tags-input.min.js'
import 'ng-tags-input/ng-tags-input.min.css'
import '#/main/core/fos-js-router/module'
import '#/main/core/modal/module'
import '#/main/core/html-truster/module'
import 'fullcalendar/dist/fullcalendar'
import 'fullcalendar/dist/locale/fr'
import 'fullcalendar/dist/locale/en-gb'
import 'angular-ui-tree'
import 'angular-bootstrap-colorpicker'
import 'angular-i18n/angular-locale_en.js'
import 'angular-i18n/angular-locale_fr.js'
import 'angular-inview'

import tinyMceConfig from './tinymce/tinymce.config'
import blogService from './blog/blog.service'
import mainController from './blog/main.controller'
import blogController from './blog/blog.controller'
import blogTemplate from './blog/blog.partial.html'
import blogEditTemplate from './blog/blogedit.partial.html'
import postController from './post/post.controller'
import postTemplate from './post/post.partial.html'
import postEditTemplate from './post/postedit.partial.html'
import postCreateTemplate from './post/postcreate.partial.html'
import messagesController from './messages/messages.controller'
import messagesTemplate from './messages/messages.partial.html'
import optionsTemplate from './blog/options.partial.html'
import postShortTemplate from './post/postShort.partial.html'
import messageTemplate from './messages/messages.template.html'
import datepickerTemplate from './post/datepicker.template.html'
import datepickerDayTemplate from './post/datepicker.day.html'
import datepickerMonthTemplate from './post/datepicker.month.html'
import datepickerYearTemplate from './post/datepicker.year.html'
import './panels/panels.module'
import './banner/banner.module'

angular
  .module('BlogModule', [
    'ui.bootstrap',
    'blog.panels',
    'blog.banner',
    'ui.translation',
    'ui.calendar',
    'ui.translation',
    'ngRoute',
    'ui.fos-js-router',
    'ui.html-truster',
    'ngResource',
    'ngFileUpload',
    'angular-loading-bar',
    'ui.tinymce',
    'ngTagsInput',
    'ui.tree',
    'colorpicker.module',
    'angular-inview'
  ])
  .value('blog.data', window.blogConfiguration)

  .run(['$templateCache', ($templateCache) => {
    $templateCache.put('message_renderer.html', messageTemplate)
    $templateCache.put('datepicker_template.html', datepickerTemplate)
    $templateCache.put('datepicker_day.html', datepickerDayTemplate)
    $templateCache.put('datepicker_month.html', datepickerMonthTemplate)
    $templateCache.put('datepicker_year.html', datepickerYearTemplate)
  }])
  .run(['$anchorScroll', ($anchorScroll) => {
    $anchorScroll.yOffset = 60
  }])

  .service('tinyMceConfig', tinyMceConfig)
  .service('blogService', blogService)
  
  .factory('Messages', () => ([]))
  .factory('blogModal', [
    '$uibModal',
    $modal => ({
      open: (template, scope, element) => $modal.open({
        template: template,
        scope: scope,
        resolve: {
          sectionToDelete: element
        }
      })
    })
  ])
  .filter('datetime', ['dateFilter', 'transFilter', (dateFilter, transFilter) => (text) => dateFilter(text, transFilter('angular_date_format', {}, 'icap_blog'))])

  .controller('postController', postController)
  .controller('messagesController', messagesController)

  .directive('blogMain', () => ({
    restrict: 'E',
    controllerAs: 'main',
    controller: mainController
  }))
  
  .directive('blogMessages', () => ({
    restrict: 'E',
    controllerAs: 'ctrl',
    controller: messagesController,
    template: messagesTemplate
  }))

  .directive('post', () => ({
    restrict: 'E',
    controllerAs: 'postCtrl',
    controller: postController,
    template: postShortTemplate
  }))

  .directive('scrollTo', ['$location', '$anchorScroll',  ($location, $anchorScroll) => {
    return (scope, element, attrs) => {
      element.bind('click', event => {
        event.stopPropagation()
        let off = scope.$on('$locationChangeStart', ev => {
          off()
          ev.preventDefault()
        })
        var location = attrs.scrollTo
        $location.hash(location)
        $anchorScroll()
      })
    }
  }])

  .config(['$routeProvider', ($routeProvider) => {
    $routeProvider
      .when('/', {
        template: blogTemplate,
        controller: blogController,
        controllerAs: 'vm',
        resolve: { // This route can benefit from a complete load of data
          func: ['$route', ($route) => {
            $route.current.params.loadPost = true
          }]
        }
      })
      .when('/edit', {
        template: blogEditTemplate,
        controller: blogController,
        controllerAs: 'vm'
      })
      .when('/configure', {
        template: optionsTemplate,
        controller: blogController,
        controllerAs: 'vm'
      })
      .when('/post/new', {
        template: postCreateTemplate,
        controller: postController,
        controllerAs: 'vm'
      })
      .when('/author/:authorId', {
        template: blogTemplate,
        controller: blogController,
        controllerAs: 'vm',
        resolve: { // This route acts as a filter
          func: ['$route', ($route) => {
            $route.current.params.is_filter = true
            $route.current.params.filter = 'author'
          }]
        }
      })
      .when('/:slug', {
        template: postTemplate,
        controller: postController,
        controllerAs: 'postCtrl',
        resolve: { // This route can benefit from a complete load of data
          func: ['$route', ($route) => {
            $route.current.params.loadPost = true
          }]
        }
      })
      .when('/:slug#comments', {
        template: blogTemplate,
        controller: blogController,
        controllerAs: 'vm',
        resolve: { // This route can benefit from a complete load of data
          func: ['$route', ($route) => {
            $route.current.params.loadPost = true
          }]
        }
      })
      .when('/:slug/edit', {
        template: postEditTemplate,
        controller: postController,
        controllerAs: 'vm',
        resolve: { // This route can benefit from a complete load of data
          func: ['$route', ($route) => {
            $route.current.params.loadPost = true
          }]
        }
      })
      .when('/tag/:slug', {
        template: blogTemplate,
        controller: blogController,
        controllerAs: 'vm',
        resolve: { // This route acts as a filter
          func: ['$route', ($route) => {
            $route.current.params.is_filter = true
            $route.current.params.filter = 'tag'
          }]
        }
      })
      .when('/search/:terms', {
        template: blogTemplate,
        controller: blogController,
        controllerAs: 'vm',
        resolve: { // This route acts as a filter
          func: ['$route', ($route) => {
            $route.current.params.is_filter = true
            $route.current.params.filter = 'search'
          }]
        }
      })
      .when('/archives/:year/:month/:day?', {
        template: blogTemplate,
        controller: blogController,
        controllerAs: 'vm',
        resolve: { // This route acts as a filter
          func: ['$route', ($route) => {
            $route.current.params.is_filter = true
            $route.current.params.filter = 'date'
          }]
        }
      })
      .otherwise('/')
  }])

angular.element(document).ready(() => {
  angular.bootstrap(angular.element(document).find('body')[0], ['BlogModule'], {
    strictDi: true
  })
})
