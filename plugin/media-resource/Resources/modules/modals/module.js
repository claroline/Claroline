import angular from 'angular/index'
import 'angular-bootstrap'
import '#/main/core/html-truster/module'
import HelpModalService from './Services/HelpModalService'
import OptionsModalService from './Services/OptionsModalService'

angular
  .module('Modals', [
    'ui.bootstrap',
    'ui.html-truster'
  ])
  .service('helpModalService', [
    '$uibModal',
    'regionsService',
    'configService',
    HelpModalService
  ])
  .service('optionsModalService', [
    '$uibModal',
    'regionsService',
    OptionsModalService
  ])
