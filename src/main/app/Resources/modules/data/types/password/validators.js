import {param} from '#/main/app/config'
import {trans} from '#/main/app/intl'
import {match} from '#/main/app/data/types/validators'

function passwordComplexity(value) {
  let error = false

  const minLength = param('authentication.minLength')
  if (minLength > 0 && value.length < minLength) {
    error = true
  }

  if (param('authentication.requireLowercase') && !match(value, {regex: /[a-z]/})) {
    error = true
  }

  if (param('authentication.requireUppercase') && !match(value, {regex: /[A-Z]/})) {
    error = true
  }

  if (param('authentication.requireNumber') && !match(value, {regex: /[0-9]/})) {
    error = true
  }

  if (param('authentication.requireSpecialChar') && !match(value, {regex: /[^a-zA-Z0-9]/})) {
    error = true
  }

  if (error) {
    return [trans('invalidPassword', {}, 'security')]
  }
}

export {
  passwordComplexity
}
