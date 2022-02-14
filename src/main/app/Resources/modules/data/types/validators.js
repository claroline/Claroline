import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'
import set from 'lodash/set'
import moment from 'moment'

import {url as urlGenerator} from '#/main/app/api/router'
import {trans, tval} from '#/main/app/intl/translation'
import {isValidDate} from '#/main/app/intl/date'
import {isHtmlEmpty} from '#/main/app/data/types/html/validators'

// TODO : break me

function notEmpty(value) {
  if (
    false === value
    || undefined === value
    || null === value
    || (!(value instanceof File) && typeof value === 'object' && isEmpty(value)) // objects and arrays (lodash isEmpty always returns true for files)
    || (typeof value === 'string' && ('' === value || '' === value.trim() || isHtmlEmpty(value))) // strings and HTML
  ) {
    return tval('This value should not be blank.')
  }
}

function notBlank(value, options = {}) {
  if (typeof value === 'string') {
    value = value.trim()
  } else if (isNaN(value)) {
    value = ''
  }

  if (value === '' || value === null || (undefined !== options.isHtml && options.isHtml && isHtmlEmpty(value))) {
    return tval('This value should not be blank.')
  }
}

function array(value) {
  if (!Array.isArray(value)) {
    return tval('This value should be an array.')
  }
}

function string(value) {
  if (typeof value !== 'string') {
    return tval('This value should be a string.')
  }
}

function number(value) {
  if (typeof value !== 'number' && (isNaN(parseFloat(value)) || !isFinite(value))) {
    return tval('This value should be a valid number.')
  }
}

function gtZero(value) {
  if (value <= 0) {
    return trans(
      'This value should be greater than {{ limit }}.',
      {},
      'validators'
    ).replace('{{ limit }}', 0)
  }
}

function gtMin(value, options) {
  if (undefined !== options.min && value < options.min) {
    return trans(
      'This value should be greater than {{ limit }}.',
      {},
      'validators'
    ).replace('{{ limit }}', options.min)
  }
}

function ltMax(value, options) {
  if (undefined !== options.max && value > options.max) {
    return trans(
      'This value should be lower than {{ limit }}.',
      {},
      'validators'
    ).replace('{{ limit }}', options.max)
  }
}

function inRange(value, options) {
  return chain(value, options, [gtMin, ltMax])
}

function lengthMin(value, options) {
  if (undefined !== options.minLength && value.length < options.minLength) {
    return trans(
      'This value should be greater than {{ limit }}.',
      {},
      'validators'
    ).replace('{{ limit }}', options.minLength)
  }
}

function lengthMax(value, options) {
  if (undefined !== options.maxLength && value.length > options.maxLength) {
    return trans(
      'This value should be lower than {{ limit }}.',
      {},
      'validators'
    ).replace('{{ limit }}', options.maxLength)
  }
}

function lengthInRange(value, options) {
  return chain(value, options, [lengthMin, lengthMax])
}

function url(/*value*/) {
  // TODO : fix regex
  /*if (match(value, {regex: /^((http[s]?|ftp):\/)?\/?([^:\/\s]+)((\/\w+)*\/)([\w\-\.]+[^#?\s]+)(.*)?(#[\w\-]+)?$/ig})) {
    return tval('This value should be a valid url.')
  }*/
}

function email(value) {
  // we use same regex than W3C <input type="email" />
  if (match(value, {regex: /^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/ig})) {
    return tval('This value should be a valid email.')
  }
}

function date(value) {
  if (!isValidDate(value)) {
    return tval('This value should be a valid date.')
  }
}

function match(value, options) {
  if (undefined !== options.regex && !options.regex.test(value)) {
    return tval('This value should match the defined format.')
  }
}

function gteZero(value) {
  if (value < 0) {
    return trans(
      'This value should be {{ limit }} or more.',
      {},
      'validators'
    ).replace('{{ limit }}', 0)
  }
}

/**
 * Disables `validator` if `condition` is not met.
 * NB. This doesn't call the validator itself.
 *
 * @param {bool}     condition
 * @param {function} validator
 *
 * @return {function}
 */
function validateIf(condition, validator) {
  if (condition) {
    return validator
  }

  // if condition is not met, we just return a func that will never throw error
  return () => undefined
}

/**
 * Applies N validators to `value`.
 * The chain stops at the first failed validator.
 *
 * @param {*}      value      - the value to validate.
 * @param {object} options    - the validation options.
 * @param {Array}  validators - the list of validators to apply.
 */
function chain(value, options, validators) {
  return validators.reduce((result, validate) => {
    return result.then(res => {
      return res || Promise.resolve(validate(value, options))
    })
  }, Promise.resolve())
}

function chainSync(value, options, validators) {
  return validators.reduce((result, validate) => {
    return result || validate(value, options)
  }, undefined)
}

/**
 *
 * @param errors
 * @param errorPath
 * @param error
 *
 * @deprecated
 */
function setIfError(errors, errorPath, error) {
  if (typeof error !== 'undefined') {
    set(errors, errorPath, error)
  }
}

function greaterOrEqual(value, limit) {
  if (value < limit) {
    return trans(
      'value_greater_or_equal_to',
      {limit: limit},
      'validators'
    )
  }
}

function lowerOrEqual(value, limit) {
  if (value > limit) {
    return trans(
      'value_lower_or_equal_to',
      {limit: limit},
      'validators'
    )
  }
}

function between(value, min, max) {
  if (value < min || value > max) {
    return trans(
      'value_between',
      {min: min, max: max},
      'validators'
    )
  }
}

function dateAfter(value, limit) {
  if (moment(value) <= moment(limit)) {
    return trans(
      'date_after',
      {limit: moment(limit).format('YYYY-MM-DD')},
      'validators'
    )
  }
}

function unique(value, options = {}) {
  if (Array.isArray(value)) {
    const errors = {}
    const sensitive = options['sensitive'] !== undefined ? options['sensitive'] : false
    value.forEach((v, index) => {
      if (!errors[index]) {
        value.forEach((vv, indexBis) => {
          if (index !== indexBis && ((sensitive && v === vv) || (!sensitive && v.toUpperCase() === vv.toUpperCase()))) {
            errors[index] = trans('value_not_unique', {}, 'validators')
          }
        })
      }
    })

    if (!isEmpty(errors)) {
      return errors
    }
  }
}

function notExist(value, options = {}) {
  if (options.unique && value) {
    let existUrl
    if (Array.isArray(options.unique.check)) {
      existUrl = [options.unique.check[0], merge({}, options.unique.check[1] || {}, {value: value})]
    } else {
      existUrl = options.unique.check
    }

    return fetch(urlGenerator(existUrl), {
      credentials: 'include',
      headers: new Headers({
        'Content-Type': 'application/json; charset=utf-8',
        // next header is required for symfony to recognize our requests as XMLHttpRequest
        // there is no spec about possible values, but this is the one expected by symfony
        // @see Symfony\Component\HttpFoundation\Request::isXmlHttpRequest
        'X-Requested-With': 'XMLHttpRequest'
      })
    })
      .then(response => {
        if (204 !== response.status) {
          return response.json()
        }

        return Promise.resolve(null)
      })
      .then(response => {
        if (response) {
          return Promise.resolve(tval(options.unique.error || 'value_not_unique'))
        }

        return Promise.resolve(undefined)
      })
  }
}

export {
  validateIf,
  chain,
  chainSync,
  setIfError,

  // validators
  array,
  string,
  notBlank,
  match,
  number,
  inRange,
  gtMin,
  ltMax,
  gtZero,
  email,
  date,
  url,
  gteZero,
  lengthMin,
  lengthMax,
  lengthInRange,
  notEmpty,
  greaterOrEqual,
  lowerOrEqual,
  between,
  dateAfter,
  unique,
  notExist
}
