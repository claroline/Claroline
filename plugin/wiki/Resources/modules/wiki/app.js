import angular from 'angular/index'
import 'angular-ui-bootstrap'
import 'angular-route'
import 'angular-ui-tinymce'
import 'angular-ui-tree'
import 'angular-loading-bar'
import 'angular-animate'
import 'angular-resource'
import 'angular-loading-bar'
import '#/main/core/innova/angular-translation'
import '#/main/core/api/router/module'
import 'angular-datetime-input'
import tinyMceConfig from './tinymce/tinymce.config'
import restInterceptor from './rest/interceptor.factory'
import wikiService from './wiki.service'
import messagesController from './messages/messages.controller'
import messagesTemplate from './messages/messages.partial.html'
import wikiTemplate from './wiki.partial.html'
import wikiController from './wiki.controller.js'
import treeController from './tree/tree.controller.js'
import treeTemplate from './tree/tree.component.html'
import treeService from './tree/tree.service'
import nodeRenderer from './tree/node_renderer.partial.html'
import sectionRenderer from './section_renderer.partial.html'
import optionsTemplate from './options.partial.html'
import sectionTemplate from './section.partial.html'
import sectionController from './section.controller'
import contributionTemplate from './contribution.partial.html'
import contributionController from './contribution.controller'
import diffTemplate from './diff.partial.html'
import diffController from './diff.controller'

angular.element(document).ready(function () {
  angular.bootstrap(angular.element(document).find('body')[0], ['WikiModule'], {
    strictDi: true
  })
})

angular
  .module('WikiModule', [
    'ui.bootstrap',
    'ngRoute',
    'ui.tinymce',
    'ui.tree',
    'angular-loading-bar',
    'ngResource',
    'angular-loading-bar',
    'ngAnimate',
    'ui.translation',
    'ui.fos-js-router',
    'cfp.loadingBar',
    'datetime'
  ])

  .run(['$templateCache', ($templateCache) => {
    $templateCache.put('node_renderer.html', nodeRenderer)
    $templateCache.put('section_renderer.html', sectionRenderer)
  }])
  .run(['$anchorScroll', ($anchorScroll) => {
    $anchorScroll.yOffset = 80
  }])

  .service('tinyMceConfig', tinyMceConfig)
  .service('WikiService', wikiService)
  .service('TreeService', treeService)

  .factory('Messages', () => ([]))
  .factory('wikiModal', [
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
  .factory('restInterceptor', [
    '$location', 'Messages', 'transFilter',
    ($location, Messages, transFilter) => new restInterceptor(
      $location, Messages, transFilter)
  ])

  .controller('wikiController', wikiController)
  .controller('treeController', treeController)
  .controller('messagesController', messagesController)
  .controller('sectionController', sectionController)
  .controller('contributionController', contributionController)
  .controller('diffController', diffController)

  .directive('dynamicHeader', ['$compile', ($compile) => {
    return {
      restrict: 'E',
      transclude: true,

      link: (scope, element, attr) => {
        let html = element.html()

        if (isFinite(attr.level)) {
          let level = attr.level * 1 + 1
          if (level < 2) { level = 2 }
          if (level > 4) { level = 4 }

          let domElement = document.createElement(`h${level}`)
          angular.element(domElement).html(html).addClass(attr.class)

          let e = $compile(domElement)(scope)
          element.replaceWith(e)
        }
      }
    }
  }])
  .directive('emitEvent', () => ({
    restrict: 'A',
    link: (scope, element, attrs) => {
      scope.$emit('ngRepeatLoop', scope.$index, scope[attrs.emitEvent])
    }
  }))
  .directive('flattenedTreeOptions', ['WikiService', (wikiService) => {

    let recur = function (tree, flattenedTree = [] , loop = '') {
      let treeLength = tree.length

      for (let i = 0; i < treeLength; i++) {
        if (tree[i].activeContribution.title) {
          let nbsps = Array(tree[i].level).join('&nbsp;')
          let numbering = `${loop}.${i+1}`.substring(3)
          let title = tree[i].activeContribution.title

          flattenedTree.push({
            'id': tree[i].id,
            'title': `${nbsps}${numbering} ${title}`
          })
        }
        if ('__children' in tree[i]) {
          recur(tree[i].__children, flattenedTree, `${loop}.${i+1}`)
        }
      }
      return flattenedTree
    }

    return {
      restrict: 'A',
      link: (scope, element) => {

        let titles = recur(wikiService.sections)
        for (let key in recur(wikiService.sections)) {
          let domElement = document.createElement('option')
          let angularElement = angular.element(domElement)
          angularElement
            .html(titles[key].title)
            .attr('value', titles[key].id)

          element.append(domElement)
        }
      }
    }
  }])
  .directive('section', () => {
    return {
      restrict: 'A',
      controller: sectionController,
      controllerAs: 'cvm',
      require: ['?^^section', 'section'],
      link: {
        pre: ($scope, element, attr, ctrl) => {
          // Pass parent section to current section controller
          if (ctrl[0] === null) {
            ctrl[1].parent = ctrl[1].wiki.sections[0]
          } else {
            ctrl[1].parent = ctrl[0]
          }
        }
      }
    }
  })
  .directive('wikiMessages', () => ({
    restrict: 'E',
    controllerAs: 'vm',
    controller: messagesController,
    template: messagesTemplate
  }))
  .directive('wikiBreadcrumbs', () => ({
    restrict: 'A',
    controller: wikiController,
    controllerAs: 'vm',
    scope: true
  }))
  .directive('wikiTree', () => ({
    restrict: 'E',
    controllerAs: 'vm',
    controller: 'treeController',
    template: treeTemplate
  }))

  .filter('trustedHtml', ['$sce', $sce => text => $sce.trustAsHtml(text)])
  .filter('htmlToPlaintext', () => (text) => text ? String(text).replace(/<[^>]+>/gm, '') : '')
  .filter('convertAPIDate', ['datetime', (datetime) => (text) => {
    try {
      let parser = datetime('yyyy-MM-dd HH:mm:ss')
      let dateToParse = String(text).substring(0, 19)
      parser.parse(dateToParse)
      return parser.getDate()
    } catch (err) {
      return String(text)
    }
  }])
  .filter('datetime', ['dateFilter', 'transFilter', (dateFilter, transFilter) => (text) => dateFilter(text, transFilter('angular_date_format', {}, 'icap_wiki'))])

  .config([
    '$routeProvider',
    '$locationProvider',
    '$httpProvider',
    ($routeProvider, $locationProvider, $httpProvider) => {

      $locationProvider.html5Mode({
        enabled: false,
        requireBase: true
      })

      $routeProvider
        .when('/', {
          template: wikiTemplate,
          controllerAs: 'vm',
          controller: wikiController,
          resolve: {
            breadcrumbs: ['$route', 'WikiService', ($route, wikiService) => {
              $route.current.params.breadcrumbs = [
                { 'title': wikiService.title }
              ]
            }]
          }
        })
        .when('/options', {
          template: optionsTemplate,
          controllerAs: 'vm',
          controller: wikiController,
          resolve: {
            breadcrumbs: ['$route', 'WikiService', 'transFilter', ($route, wikiService, transFilter) => {
              $route.current.params.breadcrumbs = [
                { 'title': wikiService.title, 'link': '/' },
                { 'title': transFilter('options', 'icap_wiki') }
              ]
            }]
          }
        })
        .when('/section/:sectionId', {
          template: sectionTemplate,
          controllerAs: 'vm',
          controller: 'sectionController',
          resolve: {
            breadcrumbs: ['$route', 'WikiService', 'transFilter', ($route, wikiService, transFilter) => {
              $route.current.params.breadcrumbs = [
                { 'title': wikiService.title, 'link': '/' },
                { 'title': transFilter('history', 'icap_wiki') }
              ]
            }]
          }
        })
        .when('/section/:sectionId/contribution/:contributionId', {
          template: contributionTemplate,
          controllerAs: 'vm',
          controller: 'contributionController',
          resolve: {
            breadcrumbs: ['$route', 'WikiService', 'transFilter', ($route, wikiService, transFilter) => {
              $route.current.params.breadcrumbs = [
                { 'title': wikiService.title, 'link': '/' },
                { 'title': transFilter('history', 'icap_wiki'), 'link': `/section/${$route.current.params.sectionId}` },
                { 'title': transFilter('contribution', 'icap_wiki') }
              ]
            }]
          }
        })
        .when('/section/:sectionId/compare/:oldId/:newId', {
          template: diffTemplate,
          controllerAs: 'vm',
          controller: 'diffController',
          resolve: {
            breadcrumbs: ['$route', 'WikiService', 'transFilter', ($route, wikiService, transFilter) => {
              $route.current.params.breadcrumbs = [
                { 'title': wikiService.title, 'link': '/' },
                { 'title': transFilter('history', 'icap_wiki'), 'link': `/section/${$route.current.params.sectionId}` },
                { 'title': transFilter('revision_comparison', 'icap_wiki') }
              ]
            }]
          }
        })
        .otherwise('/')

      $httpProvider.interceptors.push('restInterceptor')
    }
  ])
