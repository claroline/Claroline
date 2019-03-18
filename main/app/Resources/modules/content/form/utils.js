import isObject from 'lodash/isObject'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'
import mergeWith from 'lodash/mergeWith'
import omitBy from 'lodash/omitBy'

import {DataFormSection, DataFormProperty} from '#/main/app/content/form/prop-types'

function isDisplayed(element, data) {
  return typeof element.displayed === 'function' ? element.displayed(data) : element.displayed
}

function createFieldDefinition(field, data) {
  const defaultedField = merge({}, DataFormProperty.defaultProps, field)

  // adds default to linked fields if any
  if (defaultedField.linked && 0 !== defaultedField.linked.length) {
    defaultedField.linked = defaultedField.linked
      // adds default to fields
      .map(field => merge({}, DataFormProperty.defaultProps, field))
      // filters hidden fields
      .filter(field => isDisplayed(field, data))
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
      if (isDisplayed(defaultedSection, data) && (0 !== defaultedSection.fields.length || defaultedSection.render)) {
        // section has fields and is displayed keep it
        defaultedSection.fields = defaultedSection.fields
        // adds default to fields
          .map(field => createFieldDefinition(field, data))
          // filters hidden fields
          .filter(field => isDisplayed(field, data))

        return defaultedSection
      }

      // only keep the section if it has fields
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
  // manually manage arrays (omitBy works great, but it converts it into objects, which fuck up the react components)
  if (errors instanceof Array || newErrors instanceof Array) {
    if (newErrors) {
      return newErrors
        .map(error => (isObject(error) ? cleanErrors(error instanceof Array ? [] : {}, error) : error) || null)
        .filter(error => !isEmpty(error))
    }

    return errors
  }

  return omitBy(mergeWith({}, errors, newErrors, (objV, srcV) => {
    // recursive walk in sub objects
    return (isObject(srcV) ? cleanErrors(objV, srcV) : srcV) || null
  }), isEmpty)
}

export {
  createFormDefinition,
  cleanErrors
}
