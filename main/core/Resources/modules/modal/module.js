import {} from 'angular/angular.min'

import {} from 'angular-bootstrap'

import {} from '../html-truster/module'

import ConfirmModalDirective from './Directive/ConfirmModalDirective'

window.angular
  .module('ui.modal',[
    'ui.bootstrap',
    'ui.html-truster'
  ])
  .directive('confirmModal', [
    '$uibModal',
    () => new ConfirmModalDirective
  ])