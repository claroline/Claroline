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
import ClarolineAPI from '../../../../../main/core/Resources/modules/services/module'
import mainTemplate from './main.partial.html'
import studyTemplate from './study.partial.html'
import createNoteTemplate from './createNote.partial.html'
import editNoteTemplate from './editNote.partial.html'
import listNoteTemplate from './listNote.partial.html'
import editNoteTypeTemplate from './editNoteType.partial.html'
import FlashCardCtrl from './FlashCardCtrl.js'
import CreateNoteCtrl from './CreateNoteCtrl.js'
import EditNoteCtrl from './EditNoteCtrl.js'
import ListNoteCtrl from './ListNoteCtrl.js'
import EditNoteTypeCtrl from './EditNoteTypeCtrl.js'
import StudyCtrl from './StudyCtrl.js'
import FlashCardService from './FlashCardService.js'

angular
  .module('FlashCardModule', [
    'ui.bootstrap',
    'ngRoute',
    'ClarolineAPI'
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
  .controller('EditNoteCtrl', [
    'FlashCardService',
    '$routeParams',
    '$location',
    EditNoteCtrl
  ])
  .controller('ListNoteCtrl', [
    'FlashCardService',
    'ClarolineAPIService',
    ListNoteCtrl
  ])
  .controller('EditNoteTypeCtrl', [
    'FlashCardService',
    '$routeParams',
    '$location',
    EditNoteTypeCtrl
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
        .when('/edit_note/:id', {
          template: editNoteTemplate,
          bindToController: true,
          controller: 'EditNoteCtrl',
          controllerAs: 'vm'
        })
        .when('/list_notes', {
          template: listNoteTemplate,
          bindToController: true,
          controller: 'ListNoteCtrl',
          controllerAs: 'vm'
        })
        .when('/edit_note_type/:id', {
          template: editNoteTypeTemplate,
          bindToController: true,
          controller: 'EditNoteTypeCtrl',
          controllerAs: 'vm'
        })
        .otherwise({
          redirectTo: "/"
        })
    }
  ]);

