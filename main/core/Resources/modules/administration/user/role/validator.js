import {setIfError, notBlank} from '#/main/core/validation'

/**
 * Gets validation errors for a Role.
 *
 * @param   {Object} role
 *
 * @returns {Object}
 */
function validate(role) {
  const errors = {}

  setIfError(errors, 'name', notBlank(role.name))

  return errors
}

export {
  validate
}
