import angular from 'angular/index'

import template from './../Partial/show-item.html'

export default class SummaryItemShowDirective {
  constructor() {
    this.restrict = 'E'
    this.replace = true
    this.template = template
    this.controller = [
      '$routeParams',
      'PathService',
      'UserProgressionService',
      function($routeParams, PathService, UserProgressionService) {
        /**
         * Current displayed Step
         * @type {object}
         */
        this.current = $routeParams

        this.collapsed = false

        this.path = PathService.getPath()

        this.userProgression = UserProgressionService.getForStep(this.step)
        
        this.goTo = function() {
          PathService.goTo(this.step)
        }

        this.updateProgression = function(status) {
          if (!angular.isObject(this.userProgression)) {
            UserProgressionService.create(this.step, status)
          } else {
            UserProgressionService.update(this.step, status)
          }
        }
      }
    ]
    this.controllerAs = 'summaryItemShowCtrl'
    this.bindToController = true
    this.scope = {
      step: '='
    }
  }
}
