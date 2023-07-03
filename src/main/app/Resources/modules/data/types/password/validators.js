import {param} from '#/main/app/config'
import {trans} from '#/main/app/intl'

function passwordComplexity(value) {
  let error = false

  const minLength = param('authentication.minLength')
  if (minLength > 0 && value.length < minLength) {
    error = true
  }

  if (param('authentication.requireLowercase') && !value.match(/[a-z]/)) {
    error = true
  }

  if (param('authentication.requireUppercase') && !value.match(/[A-Z]/)) {
    error = true
  }

  if (param('authentication.requireNumber') && !value.match(/[0-9]/)) {
    error = true
  }

  if (param('authentication.requireSpecialChar') && !value.match(/[^a-zA-Z0-9]/)) {
    error = true
  }

  if (error) {
    return [trans('invalidPassword', {}, 'security')]
  }
}


export {
  passwordComplexity
}
