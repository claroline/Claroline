import angular from 'angular/index'
import 'angular-bootstrap'
import 'angular-resource'
import 'angular-ui-translation/angular-translation'
import 'angular-loading-bar'
import '#/main/core/fos-js-router/module'
import bibliographyController from './bibliography.controller.js'



angular
  .module('BibliographyModule', [
    'ui.fos-js-router',
    'ui.bootstrap',
    'ngResource',
    'ui.translation',
    'angular-loading-bar'
  ])
  .factory('Messages', () => ([]))
  .controller('bibliographyController', bibliographyController)

angular.element(document).ready(function () {
  angular.bootstrap(angular.element(document).find('div#icap-bibliography-modal')[0], ['BibliographyModule'], {
    strictDi: true
  })
})