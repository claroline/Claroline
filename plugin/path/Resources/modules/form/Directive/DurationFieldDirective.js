import template from './../Partial/duration-field.html'

export default class DurationFieldDirective {
  constructor() {
    // Step for each increment on the hour field
    this.stepHour = 1

    // Step for each increment on the minute field
    this.stepMinute = 5

    this.restrict = 'E'
    this.replace = true
    this.template = template
    this.scope = {
      model: '='
    }
    this.link = function (scope) {
      scope.$watch('model', function (newValue) {
        scope.hours   = 0
        scope.minutes = 0

        if (newValue) {
          var minutes = parseInt(scope.model / 60)

          scope.hours   = parseInt(minutes / 60)
          scope.minutes = parseInt(minutes % 60)
        }
      })

      scope.incrementDuration = function (type) {
        if ('hour' === type) {
          scope.hours += this.stepHour
        }
        else if ('minute' === type && (scope.minutes + this.stepMinute) < 60) {
          scope.minutes += this.stepMinute
        }

        scope.recalculate()
      }.bind(this)

      scope.decrementDuration = function (type) {
        if ('hour' === type && (scope.hours - this.stepHour) >= 0) { // Negative values are not allowed
          scope.hours -= this.stepHour
        }
        else if ('minute' === type && (scope.minutes - this.stepMinute) >= 0) { // Negative values are not allowed
          scope.minutes -= this.stepMinute
        }

        scope.recalculate()
      }.bind(this)

      scope.correctDuration = function (type) {
        // Don't allow negative value, so always return absolute value
        if ('hour' === type) {
          scope.hours = Math.abs(scope.hours)
        }
        else if ('minute' === type) {
          scope.minutes = Math.abs(scope.minutes)

          // Don't allow more than 60 minutes
          var minutesToHours = Math.floor(scope.minutes / 60)
          if (minutesToHours > 0) {
            scope.hours += minutesToHours
            scope.minutes = scope.minutes % 60
          }
        }

        scope.recalculate()
      }

      scope.recalculate = function () {
        scope.model = (scope.hours * 3600) + (scope.minutes * 60)
      }
    }
  }
}
