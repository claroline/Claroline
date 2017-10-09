import {setIfError, notBlank} from '#/main/core/validation'

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
  validate
}
