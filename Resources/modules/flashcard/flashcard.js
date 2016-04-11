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
import mainTemplate from './main.partial.html'
import FlashCardCtrl from './FlashCardCtrl.js'
import FlashCardService from './FlashCardService.js'

angular
  .module('FlashCardModule', [
    'ui.bootstrap',
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
    'FlashCardModal',
    FlashCardCtrl
  ])
  .directive('flashcard', () => ({
    controllerAs: 'vm',
    bindToController: true,
    controller: 'FlashCardCtrl',
    template: mainTemplate
  }))
  .filter('trans', () => (string, domain = 'platform') =>
    Translator.trans(string, domain)
  )
