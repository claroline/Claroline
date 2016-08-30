/**
 * Clipboard module
 */

import angular from 'angular/index'

import ClipboardService from './Service/ClipboardService'

angular
  .module('Clipboard', [])
  .service('ClipboardService', [
    ClipboardService
  ])