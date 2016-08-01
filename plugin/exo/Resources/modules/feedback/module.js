/**
 * Feedback module
 */

import angular from 'angular/index'
import FeedbackService from './Services/FeedbackService'

angular
  .module('Feedback', [])
  .service('FeedbackService', [
    '$log',
    FeedbackService
  ])
