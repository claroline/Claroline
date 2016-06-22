import FieldController from '../FieldController'
import Email from '../../Validator/Email'

export default class EmailController {
  constructor ($scope) {
    this.$scope = $scope
    $scope.$watch(() => {

      return this.field}, (newValue, oldValue) => {
          this.emailField = this.getEmailField(this.field)
    })

    this.emailField = this.getEmailField(this.field)
  }

  getEmailField(field) {
      const emailField = angular.copy(field)
      emailField[1] = 'text'
      const options = emailField[2] || {}
      const validators = options.validators || []
      validators.push(new Email())
      emailField[2].validators = validators

      return emailField
  }
}
