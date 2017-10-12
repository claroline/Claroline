import isEmpty from 'lodash/isEmpty'

import {setIfError, notBlank} from '#/main/core/validation'

/**
 * Checks if an Announce data are valid.
 *
 * @param   {Object} theme
 *
 * @returns {boolean}
 */
function isValid(theme) {
  return isEmpty(validate(theme))
}

/**
 * Gets validation errors for an Announce.
 *
 * @param   {Object} announce
 *
 * @returns {Object}
 */
function validate(announce) {
  const errors = {}

  setIfError(errors, 'content', notBlank(announce.content))

  return errors
}

export {
  isValid,
  validate
}
