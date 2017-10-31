import isEmpty from 'lodash/isEmpty'

import {setIfError, notBlank, notEmptyArray} from '#/main/core/validation'

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

  if (announce.meta.notifyUsers === 2) {
    setIfError(errors, 'meta.notificationDate', notBlank(announce.meta.notificationDate))
  }
  if (announce.meta.notifyUsers !== 0) {
    setIfError(errors, 'roles', notEmptyArray(announce.roles))
  }

  return errors
}

export {
  isValid,
  validate
}
