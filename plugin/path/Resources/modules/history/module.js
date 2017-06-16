/**
 * History module
 */

import angular from 'angular/index'

import HistoryService from './Service/HistoryService'

angular
  .module('History', [])
  .service('HistoryService', [
    HistoryService
  ])