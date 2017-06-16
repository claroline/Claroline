/**
 * Confirm module
 */

import angular from 'angular/index'
import 'angular-bootstrap'

import ConfirmService from './Service/ConfirmService'
import ConfirmModalCtrl from './Controller/ConfirmModalCtrl'

angular
  .module('Confirm', [
    'ui.bootstrap'
  ])
  .service('ConfirmService', [
    '$uibModal',
    ConfirmService
  ])
  .controller('ConfirmModalCtrl', [
    '$scope',
    '$uibModalInstance',
    'title',
    'message',
    'confirmButton',
    ConfirmModalCtrl
  ])
