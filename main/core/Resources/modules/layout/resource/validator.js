import isEmpty from 'lodash/isEmpty'
import set from 'lodash/set'

import {tval} from '#/main/core/translation'
import {setIfError, notBlank} from '#/main/core/validation'
import {closeTargets} from '#/main/core/layout/resource/enums'

/**
 * Checks if a ResourceNode data are valid.
 *
 * @param   {Object} resourceNode
 *
 * @returns {boolean}
 */
function isValid(resourceNode) {
  return isEmpty(validate(resourceNode))
}

/**
 * Gets validation errors for a ResourceNode.
 *
 * @param   {Object} resourceNode
 *
 * @returns {Object}
 */
function validate(resourceNode) {
  const errors = {}

  setIfError(errors, 'name', notBlank(resourceNode.name))

  const currentTarget = closeTargets.filter(target => resourceNode.parameters.closeTarget === target[0])
  if (isEmpty(currentTarget)) {
    set(errors, 'parameters.closeTarget', tval('This value is not valid.'))
  }

  return errors
}

export {
  isValid,
  validate
}
