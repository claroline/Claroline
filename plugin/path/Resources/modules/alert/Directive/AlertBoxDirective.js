import template from './../Partial/alert-box.html'

/**
 * Alert box directive
 */
export default class AlertBoxDirective {
  constructor(AlertService) {
    this.restrict = 'E'
    this.replace = true
    this.template = template
    this.scope = {}
    this.link = function (scope) {
      scope.current = AlertService.getCurrent()

      scope.closeCurrent  = function () {
        AlertService.closeCurrent()
      }
    }
  }
}
