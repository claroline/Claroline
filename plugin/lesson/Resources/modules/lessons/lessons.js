import angular from 'angular/index'
import 'angular-ui-bootstrap'
import 'angular-route'
import 'angular-ui-tinymce'
import 'angular-ui-tree'
import 'angular-loading-bar'
import 'angular-animate'
import '#/main/core/innova/angular-translation'

import tinyMceConfig from './tinymce/tinymce.config'

import restService from './rest/rest.service'
import restInterceptor from './rest/interceptor.factory'

import mainController from './main.controller'

import chapterTemplate from './chapter/chapter.component.html'
import chapterController from './chapter/chapter.controller'
import chapterFactory from './chapter/chapter.factory'

import noChapterTemplate from './chapter/noChapter.component.html'

import createChapterTemplate from './chapter/newChapter.component.html'
import updateChapterTemplate from './chapter/editChapter.component.html'
import moveChapterTemplate from './chapter/moveChapter.component.html'
import duplicateChapterTemplate from './chapter/duplicateChapter.component.html'
import notFound from './chapter/notFound.partial.html'

import treeTemplate from './tree/tree.component.html'
import treeController from './tree/tree.controller'
import treeService from './tree/tree.service'

import alertsTemplate from './alerts.partial.html'

angular.element(document).ready(function () {
  angular.bootstrap(angular.element(document).find('body')[0], ['LessonModule'], {
    strictDi: true
  })
})

angular
  .module('LessonModule', [
    'ui.bootstrap',
    'ngRoute',
    'ui.tinymce',
    'ui.tree',
    'angular-loading-bar',
    'ui.translation'
  ])

  .service('tinyMceConfig', tinyMceConfig)

  .service('restService', restService)
  .factory('restInterceptor', ['$location', 'Alerts', ($location, Alerts) => new restInterceptor($location, Alerts)])

  .factory('lessonModal', [
    '$uibModal',
    $modal => ({
      open: (template, scope) => $modal.open({
        template: template,
        scope: scope
      })
    })
  ])

  .factory('Alerts', () => ([]))
  .factory('Chapter', () => new chapterFactory())
  .service('Tree', treeService)

  .controller('mainController', mainController)
  .controller('chapterController', chapterController)
  .controller('treeController', treeController)

  .directive('noChapter', () => ({
    controllerAs: 'cc',
    controller: 'chapterController',
    template: noChapterTemplate
  }))
  .directive('lessonAlerts', () => ({
    restrict: 'E',
    template: alertsTemplate
  }))
  .directive('lessonTree', () => ({
    restrict: 'E',
    controllerAs: 'vm',
    controller: 'treeController',
    template: treeTemplate
  }))
  .directive('confirmDiscardChanges', ['transFilter', (transFilter) => ({
    restrict: 'A',
    require: 'form',
    link: ($scope, element, attrs, controller) => {
      $scope.$on('$locationChangeStart', (event) => {
        if (controller.$dirty && !controller.$submitted) {
          if(!window.confirm(transFilter('unsaved_changes', {}, 'icap_lesson'))) {
            event.preventDefault()
          }
        }
      })
    }
  })])
  .directive('giveFocus', ['$timeout', ($timeout) => ({
    restrict: 'A',
    link: ($scope, element) => {
      $timeout(function () {
        element[0].focus()
      })
    }
  })])

  .filter('trustedHtml', ['$sce', $sce => text => $sce.trustAsHtml(text)])
  .filter('prettyJSON', () => json => JSON.stringify(json, null, ' '))

  .config([
    '$routeProvider',
    '$httpProvider',
    ($routeProvider, $httpProvider) => {
      $routeProvider
        .when('/', {
          template: chapterTemplate,
          controllerAs: 'vm',
          controller: 'chapterController'
        })
        .when('/new', {
          template: createChapterTemplate,
          controllerAs: 'vm',
          controller: 'chapterController'
        })
        .when('/:slug', {
          template: chapterTemplate,
          controllerAs: 'vm',
          controller: 'chapterController'
        })
        .when('/:slug/new', {
          template: createChapterTemplate,
          controllerAs: 'vm',
          controller: 'chapterController'
        })
        .when('/:slug/edit', {
          template: updateChapterTemplate,
          controllerAs: 'vm',
          controller: 'chapterController'
        })
        .when('/:slug/move', {
          template: moveChapterTemplate,
          controllerAs: 'vm',
          controller: 'chapterController'
        })
        .when('/:slug/duplicate', {
          template: duplicateChapterTemplate,
          controllerAs: 'vm',
          controller: 'chapterController'
        })
        .when('/error/404', {
          template: notFound
        })
        .otherwise('/')

      $httpProvider.interceptors.push('restInterceptor')
    }
  ])