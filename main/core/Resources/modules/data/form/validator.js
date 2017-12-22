import get from 'lodash/get'
import set from 'lodash/set'
import merge from 'lodash/merge'

import {chain, notEmpty, validateIf} from '#/main/core/validation'
import {getTypeOrDefault} from '#/main/core/data'

/**
 * Validates a value based on a definition object.
 *
 * @param {object} propDef   - the data definition (@see prop-types/DataFormProperty.propTypes).
 * @param {mixed}  propValue - the value to validate.
 *
 * @return {object} - the errors thrown.
 */
function validateProp(propDef, propValue) {
  const errors = {}

  // get corresponding type
  const propType = getTypeOrDefault(propDef.type)

  if (propDef.displayed) {
    // only validate displayed props
    set(errors, propDef.name, chain(propValue, propDef.options || {}, [
      // checks if not empty when field is required
      validateIf(propDef.required, notEmpty),
      // execute data type validator if any
      validateIf(propType.validate, propType.validate),
      // execute form instance validator if any
      validateIf(propDef.validate, propDef.validate)
    ]))
  }

  return errors
}

/**
 * Validates data based on a definition.
 *
 * @param {object} definition
 * @param {object} data
 *
 * @return {object}
 */
function validateDefinition(definition, data) {
  let errors = {}

  // flatten sections fields
  let formProps = []
  definition.sections.map(section => {
    formProps = formProps.concat(section.fields)
    if (section.advanced && section.advanced.fields) {
      formProps = formProps.concat(section.fields)
    }
  })

  // validate fields
  formProps.map(formProp => {
    errors = merge(errors, validateProp(formProp, get(data, formProp.name)))
  })

  return errors
}

export {
  validateDefinition,
  validateProp
}
