/*
 * This file is part of the Claroline Connect package.
 * 
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view
 * the LICENSE
 * file that was distributed with this source code.
 */

import angular from 'angular/index'
import {} from 'angular-bootstrap'
import {} from 'angular-route'
import mainTemplate from './main.partial.html'
import studyTemplate from './study.partial.html'
import createNoteTemplate from './createNote.partial.html'
import FlashCardCtrl from './FlashCardCtrl.js'
import CreateNoteCtrl from './CreateNoteCtrl.js'
import StudyCtrl from './StudyCtrl.js'
import FlashCardService from './FlashCardService.js'

angular
  .module('FlashCardModule', [
    'ui.bootstrap',
    'ngRoute',
  ])
  .service('FlashCardService', [
    '$http',
    FlashCardService
  ])
  .factory('FlashCardModal', [
    '$uibModal',
    $modal => ({
      open: template => $modal.open({ template })
    })
  ])
  .controller('FlashCardCtrl', [
    'FlashCardService',
    FlashCardCtrl
  ])
  .controller('StudyCtrl', [
    'FlashCardService',
    StudyCtrl
  ])
  .controller('CreateNoteCtrl', [
    'FlashCardService',
    CreateNoteCtrl
  ])
  .filter('trans', () => (string, domain = 'platform') =>
    Translator.trans(string, domain)
  )
  .config(["$routeProvider",
    $routeProvider => {
      $routeProvider
        .when('/', {
          template: mainTemplate,
          bindToController: true,
          controller: 'FlashCardCtrl',
          controllerAs: 'vm'
        })
        .when('/study', {
          template: studyTemplate,
          bindToController: true,
          controller: 'StudyCtrl',
          controllerAs: 'vm'
        })
        .when('/create_note', {
          template: createNoteTemplate,
          bindToController: true,
          controller: 'CreateNoteCtrl',
          controllerAs: 'vm'
        })
        .otherwise({
          redirectTo: "/"
        })
    }
  ]);

