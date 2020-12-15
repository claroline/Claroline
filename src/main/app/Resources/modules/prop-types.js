import {PropTypes} from 'prop-types'
import merge from 'lodash/merge'

function implementType(implementation, implementedType, propTypes = {}, defaultProps = {}) {
  // merge types
  implementation.propTypes = merge(implementation.propTypes || {}, implementedType.propTypes || {}, propTypes)
  // merge defaults
  implementation.defaultProps = merge(implementation.defaultTypes || {}, implementedType.defaultProps || {}, defaultProps)

  return implementation
}

/**
 *
 * @param {object}                          implementation
 * @param {Array|{propTypes, defaultTypes}} implementedTypes - the type to implement
 * @param {object}                          propTypes    - custom propTypes of the implementation
 * @param {object}                          defaultProps - custom propTypes of the implementation
 *
 * @returns {*}
 */
function implementPropTypes(implementation, implementedTypes, propTypes = {}, defaultProps = {}) {
  if (implementedTypes instanceof Array) {
    // implement all defined types
    implementedTypes.map(implementedType =>
      implementType(implementation, implementedType)
    )

    // add custom
    implementType(implementation, {propTypes, defaultProps})
  } else {
    // implement a single type
    implementType(implementation, implementedTypes, propTypes, defaultProps)
  }


  return implementation
}

export {
  PropTypes, // reexport base api
  implementPropTypes
}
