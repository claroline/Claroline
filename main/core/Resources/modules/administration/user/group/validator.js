import {setIfError, notBlank} from '#/main/core/validation'

/**
 * Gets validation errors for a Group.
 *
 * @param   {Object} group
 *
 * @returns {Object}
 */
function validate(group) {
  const errors = {}

  setIfError(errors, 'name', notBlank(group.name))

  return errors
}

export {
  validate
}
