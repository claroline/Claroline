// todo : move in data module

import {chain, notEmpty, validateIf} from '#/main/app/data/types/validators'
import {getType} from '#/main/app/data/types'

/**
 * Validates a value based on a definition object.
 *
 * @param {object} propDef   - the data definition (@see prop-types/DataFormProperty.propTypes).
 * @param {*}      propValue - the value to validate.
 *
 * @return {object} - the errors thrown.
 */
function validateProp(propDef, propValue) {
  // memoize empty validator to avoid multiple checks which can be costly (for html)
  const empty = notEmpty(propValue)

  if (propDef.type) {
    return getType(propDef.type).then(propType => {
      return Promise.resolve(chain(propValue, propDef.options || {}, [
        // checks if not empty when field is required
        validateIf(propDef.required, () => empty),
        // execute data type validator if any and value is not empty
        validateIf(!empty && propType.validate, propType.validate),
        // execute form instance validator if any value is not empty
        validateIf(!empty && propDef.validate, propDef.validate)
      ]))
    })
  }

  return Promise.resolve(chain(propValue, propDef.options || {}, [
    // checks if not empty when field is required
    validateIf(propDef.required, () => empty),
    // execute form instance validator if any value is not empty
    validateIf(!empty && propDef.validate, propDef.validate)
  ]))
}

export {
  validateProp
}
