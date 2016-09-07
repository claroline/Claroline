
import template from './../Partial/authorization-block.html'

export default class AuthorizationBlockDirective {
  constructor() {
    this.restrict = 'E'
    this.replace = 'true'
    this.template = template
    this.controllerAs = 'authorizationBlockCtrl'
    this.controller = [
      'UserProgressionService',
      function (UserProgressionService) {
        this.progression = UserProgressionService.getForStep(this.step)

        this.callForUnlock = function () {
          UserProgressionService.callForUnlock(this.step)
        }
      }
    ]
    this.scope = {
      step: '=',
      authorization: '='
    }
    this.bindToController = true
  }
}
