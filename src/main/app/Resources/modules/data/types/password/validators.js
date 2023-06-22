import {param} from '#/main/app/config'
import {trans} from '#/main/app/intl'
import {match} from '#/main/app/data/types/validators'

function passwordComplexity(value) {
  let isValid = true

  const minLength = param('authentication.minLength')

  if (minLength > 0 && value.length < minLength) {
    isValid = false
  }

  if (param('authentication.requireLowercase') && match(value, {regex: /[a-z]/})) {
    isValid = false
  }

  if (param('authentication.requireUppercase') && match(value, {regex: /[A-Z]/})) {
    isValid = false
  }

  if (param('authentication.requireNumber') && match(value, {regex: /[0-9]/})) {
    isValid = false
  }

  if (param('authentication.requireSpecialChar') && match(value, {regex: /[^a-zA-Z0-9]/})) {
    isValid = false
  }

  if(!isValid) {
    return [trans('Le mot de passe saisi est invalide.', {}, 'validators')]
  }
}

export {
  passwordComplexity
}
