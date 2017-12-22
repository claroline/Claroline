import {setIfError, notBlank} from '#/main/core/validation'

/**
 * Gets validation errors for a Location.
 *
 * @param   {Object} location
 *
 * @returns {Object}
 */
function validate(location) {
  const errors = {}

  setIfError(errors, 'name', notBlank(location.name))

  return errors
}

export {
  validate
}
