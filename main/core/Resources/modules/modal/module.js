import angular from 'angular'

import {} from 'angular-ui-bootstrap'

import {} from '../html-truster/module'

import ConfirmModalDirective from './Directive/ConfirmModalDirective'

angular
  .module('ui.modal',[
    'ui.bootstrap',
    'ui.html-truster'
  ])
  .directive('confirmModal', [
    '$uibModal',
    () => new ConfirmModalDirective
  ])