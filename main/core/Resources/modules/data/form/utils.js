import isObject from 'lodash/isObject'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'
import mergeWith from 'lodash/mergeWith'
import omitBy from 'lodash/omitBy'

import {DataFormSection, DataFormProperty} from '#/main/core/data/form/prop-types'

function isFieldDisplayed(field, data) {
  return typeof field.displayed === 'function' ? field.displayed(data) : field.displayed
}

function createFieldDefinition(field, data) {
  const defaultedField = merge({}, DataFormProperty.defaultProps, field)

  // adds default to linked fields if any
  if (defaultedField.linked && 0 !== defaultedField.linked.length) {
    defaultedField.linked = defaultedField.linked
      // adds default to fields
      .map(field => merge({}, DataFormProperty.defaultProps, field))
      // filters hidden fields
      .filter(field => isFieldDisplayed(field, data))
  }

  return defaultedField
}

/**
 * Fills definition with missing default values.
 * (It excludes sections with no fields)
 *
 * @param {Array}  sections
 * @param {object} data
 *
 * @return {Array} - the defaulted definition
 */
function createFormDefinition(sections, data) {
  return sections
    .map(section => {
      // adds defaults to the section configuration
      const defaultedSection = merge({}, DataFormSection.defaultProps, section)
      if (defaultedSection.displayed) {
        // fields
        if (defaultedSection.fields && 0 !== defaultedSection.fields.length) {
          defaultedSection.fields = defaultedSection.fields
            // adds default to fields
            .map(field => createFieldDefinition(field, data))
            // filters hidden fields
            .filter(field => isFieldDisplayed(field, data))
        }

        // advanced fields
        if (defaultedSection.advanced && defaultedSection.advanced.fields && 0 !== defaultedSection.advanced.fields.length) {
          defaultedSection.advanced.fields = defaultedSection.advanced.fields
            .map(field => createFieldDefinition(field, data))
            .filter(field => isFieldDisplayed(field, data))
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
