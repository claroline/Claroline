import set from 'lodash/set'
import {trans} from './translate'
import {extractTextFromHtml} from './utils'

const tval = msg => trans(msg, {}, 'validators')

export function notBlank(value, isHtml = false) {
  if (typeof value === 'string') {
    value = value.trim()
  } else if (isNaN(value)) {
    value = ''
  }

  if (value === '' || value === null || isHtml && !extractTextFromHtml(value)) {
    return tval('This value should not be blank.')
  }
}

export function number(value) {
  if (typeof value !== 'number' && isNaN(parseFloat(value))) {
    return tval('This value should be a valid number.')
  }
}

export function gteZero(value) {
  if (value < 0) {
    return trans(
      'This value should be {{ limit }} or more.',
      {},
      'validators'
    ).replace('{{ limit }}', 0)
  }
}

export function chain(value, validators) {
  return validators.reduce((result, validate) => {
    return result || validate(value)
  }, undefined)
}

export function setIfError(errors, errorPath, error) {
  if (typeof error !== 'undefined') {
    set(errors, errorPath, error)
  }
}
