
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
      function ($routeParams, PathService, UserProgressionService) {
        /**
         * Current displayed Step
         * @type {object}
         */
        this.current = $routeParams

        this.collapsed = false

        this.path = PathService.getPath()

        this.userProgression = UserProgressionService.getForStep(this.step)
        
        this.goTo = function () {
          PathService.goTo(this.step)
        }

        this.getProgressionText = function () {
          let text = 'user_progression_step_unseen'

          switch (this.userProgression.status) {
            case 'seen':
              text = 'user_progression_step_seen'
              break

            case 'to_do':
              text = 'user_progression_step_to_do'
              break

            case 'to_review':
              text = 'user_progression_step_to_review'
              break

            case 'done':
              text = 'user_progression_step_done'
              break
          }

          return text
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
