import isObject from 'lodash/isObject'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'
import mergeWith from 'lodash/mergeWith'
import omitBy from 'lodash/omitBy'

import {DataFormSection, DataFormProperty} from '#/main/core/data/form/prop-types'

/**
 * Fills definition with missing default values.
 * (It excludes sections with no fields)
 *
 * @todo todo add defaults to advanced sections
 *
 * @param {Array} sections
 *
 * @return {Array} - the defaulted definition
 */
function createFormDefinition(sections) {
  return sections
    .map(section => {
      // adds defaults to the section configuration
      const defaultedSection = merge({}, DataFormSection.defaultProps, section)
      if (!!defaultedSection.displayed && defaultedSection.fields) {
        // adds defaults to the field configuration
        const defaultedFields = defaultedSection.fields.map(field => merge({}, DataFormProperty.defaultProps, field))
        const displayedFields = defaultedFields.filter(field => !!field.displayed)
        if (0 < displayedFields.length) {
          defaultedSection.fields = displayedFields

          return defaultedSection
        }
      }

      return null
    })
    .filter(section => null !== section)
}

/**
 * Removes errors that are now irrelevant (aka undefined) from an error object.
 *
 * @param {object} errors    - the previous error object
 * @param {object} newErrors - the new error object (removed errors are set to `undefined`)
 */
function cleanErrors(errors, newErrors) {
  return omitBy(mergeWith({}, errors, newErrors, (objV, srcV) => {
    // recursive walk in sub objects
    return (isObject(srcV) ? cleanErrors(objV, srcV) : srcV) || null
  }), isEmpty)
}

export {
  createFormDefinition,
  cleanErrors
}
