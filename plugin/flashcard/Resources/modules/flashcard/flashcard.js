/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view
 * the LICENSE
 * file that was distributed with this source code.
 *
 */

import angular from 'angular/index'
import {} from 'angular-bootstrap'
import {} from 'angular-route'
import '#/main/core/services/module'
import '#/main/core/form/module'
import mainTemplate from './main.partial.html'
import studyTemplate from './study.partial.html'
import createNoteTemplate from './createNote.partial.html'
import editNoteTemplate from './editNote.partial.html'
import listNoteTemplate from './listNote.partial.html'
import editNoteTypeTemplate from './editNoteType.partial.html'
import editDefaultParamTemplate from './editDefaultParam.partial.html'
import editUserParamTemplate from './editUserParam.partial.html'
import FlashCardCtrl from './FlashCardCtrl.js'
import CreateNoteCtrl from './CreateNoteCtrl.js'
import EditNoteCtrl from './EditNoteCtrl.js'
import ListNoteCtrl from './ListNoteCtrl.js'
import EditNoteTypeCtrl from './EditNoteTypeCtrl.js'
import StudyCtrl from './StudyCtrl.js'
import EditDefaultParamCtrl from './EditDefaultParamCtrl.js'
import EditUserParamCtrl from './EditUserParamCtrl.js'
import FlashCardService from './FlashCardService.js'

angular
  .module('FlashCardModule', [
    'ui.bootstrap',
    'ngRoute',
    'ClarolineAPI',
    'FormBuilder'
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
    '$location',
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
  .controller('EditDefaultParamCtrl', [
    'FlashCardService',
    '$routeParams',
    '$location',
    EditDefaultParamCtrl
  ])
  .controller('EditUserParamCtrl', [
    'FlashCardService',
    '$routeParams',
    '$location',
    EditUserParamCtrl
  ])
  .filter('trans', () => (string, domain = 'platform') =>
    window.Translator.trans(string, domain)
  )
  .config(['$routeProvider',
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
        .when('/edit_default_param', {
          template: editDefaultParamTemplate,
          bindToController: true,
          controller: 'EditDefaultParamCtrl',
          controllerAs: 'vm'
        })
        .when('/edit_user_param', {
          template: editUserParamTemplate,
          bindToController: true,
          controller: 'EditUserParamCtrl',
          controllerAs: 'vm'
        })
        .otherwise({
          redirectTo: '/'
        })
    }
  ])
