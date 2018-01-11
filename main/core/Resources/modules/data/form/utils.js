import isObject from 'lodash/isObject'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'
import mergeWith from 'lodash/mergeWith'
import omitBy from 'lodash/omitBy'

import {DataFormSection, DataFormProperty} from '#/main/core/data/form/prop-types'

function createFieldDefinition(field) {
  const defaultedField = merge({}, DataFormProperty.defaultProps, field)

  // adds default to linked fields if any
  if (defaultedField.linked && 0 !== defaultedField.linked.length) {
    const defaultedLinkedFields = defaultedField.linked.map(field => merge({}, DataFormProperty.defaultProps, field))
    defaultedField.linked = defaultedLinkedFields.filter(field => !!field.displayed)
  }

  return defaultedField
}

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
      if (defaultedSection.displayed) {
        // fields
        if (defaultedSection.fields && 0 !== defaultedSection.fields.length) {
          const defaultedFields = defaultedSection.fields.map(field => createFieldDefinition(field))
          defaultedSection.fields = defaultedFields.filter(field => !!field.displayed)
        }

        // advanced fields
        if (defaultedSection.advanced && defaultedSection.advanced.fields && 0 !== defaultedSection.advanced.fields.length) {
          const defaultedAdvancedFields = defaultedSection.advanced.fields.map(field => createFieldDefinition(field))
          defaultedSection.advanced.fields = defaultedAdvancedFields.filter(field => !!field.displayed)
        }

        if (0 < defaultedSection.fields.length || (defaultedSection.advanced && 0 < defaultedSection.advanced.fields.length)) {
          // only keep the section if it has fields or advanced fields
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
