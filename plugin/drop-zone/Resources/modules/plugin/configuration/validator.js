import isEmpty from 'lodash/isEmpty'

import {setIfError, notBlank} from '#/main/app/data/types/validators'

import {constants} from '#/plugin/drop-zone/plugin/configuration/constants'

/**
 * Checks if a DropzoneTool data are valid.
 *
 * @param   {Object} tool
 *
 * @returns {boolean}
 */
function isValid(tool) {
  return isEmpty(validate(tool))
}

/**
 * Gets validation errors for a DropzoneTool.
 *
 * @param   {Object} tool
 *
 * @returns {Object}
 */
function validate(tool) {
  const errors = {}

  setIfError(errors, 'name', notBlank(tool.name))

  if (tool.type === constants.compilatioValue) {
    setIfError(errors, 'url', notBlank(tool.data.url))
    setIfError(errors, 'key', notBlank(tool.data.key))
  }

  return errors
}

export {
  isValid,
  validate
}
