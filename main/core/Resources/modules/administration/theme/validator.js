import isEmpty from 'lodash/isEmpty'

import {tval} from '#/main/core/translation'
import {setIfError, notBlank} from '#/main/core/validation'

/**
 * Checks if a Theme data are valid.
 *
 * @param   {Object} theme
 *
 * @returns {boolean}
 */
function isValid(theme) {
  return isEmpty(validate(theme))
}

/**
 * Gets validation errors for a Theme.
 *
 * @param   {Object} theme
 *
 * @returns {Object}
 */
function validate(theme) {
  const errors = {}

  setIfError(errors, 'name', notBlank(theme.name))

  return errors
}

export {
  isValid,
  validate
}
