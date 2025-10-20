import isObject from 'lodash/isObject'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'
import mergeWith from 'lodash/mergeWith'
import omitBy from 'lodash/omitBy'

import {DataFormSection, DataFormProperty} from '#/main/app/content/form/prop-types'
import {toKey} from '#/main/app/utils/text'
import get from 'lodash/get'

function isFieldDisplayed(element, data) {
  return typeof element.displayed === 'function' ? element.displayed(data) : element.displayed
}

function createFieldDefinition(field, locked = [], data) {
  const defaultedField = merge({}, DataFormProperty.defaultProps, field)
  if (-1 !== locked.indexOf(field.name)) {
    defaultedField.disabled = true
  }

  // adds default to linked fields if any
  if (defaultedField.linked && 0 !== defaultedField.linked.length) {
    defaultedField.linked = createFieldsetDefinition(defaultedField.linked, locked, data)
  }

  return defaultedField
}

function createFieldsetDefinition(fields, locked = [], data) {
  return fields
    // adds default to fields
    .map(field => createFieldDefinition(field, locked, data))
    // filters hidden fields
    .filter(field => isFieldDisplayed(field, data))
}

/**
 * Fills definition with missing default values.
 * (It excludes sections with no fields)
 *
 * @param {Array}  sections
 * @param {Array}  locked
 * @param {object} data
 *
 * @return {Array} - the defaulted definition
 */
function createFormDefinition(sections, locked = [], data) {
  return sections
    .map(section => {
      // adds defaults to the section configuration
      const defaultedSection = merge({}, DataFormSection.defaultProps, section)
      if (isFieldDisplayed(defaultedSection, data)) {
        // section has fields and is displayed keep it
        if (!isEmpty(defaultedSection.fields)) {
          defaultedSection.fields = createFieldsetDefinition(defaultedSection.fields, locked, data)
        }

        if (0 !== defaultedSection.fields.length || defaultedSection.component || defaultedSection.render) {
          return defaultedSection
        }

        return null
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
      const updatedErrors = newErrors
        .map(error => (isObject(error) ? cleanErrors(error instanceof Array ? [] : {}, error) : error) || null)

      const filtered = updatedErrors.filter(error => !isEmpty(error))
      if (0 !== filtered.length) {
        // it remains some errors in the array
        // we don't filter null values to keep correct indexes
        return updatedErrors
      } else {
        return []
      }
    }

    return errors
  }

  return omitBy(mergeWith({}, errors, newErrors, (objV, srcV) => {
    // recursive walk in sub objects
    return (isObject(srcV) ? cleanErrors(objV, srcV) : srcV) || null
  }), isEmpty)
}

function getSectionId(section, formId = null) {
  let id = formId ? `${formId}-` : ''

  id += section.id || toKey(section.title)

  return id
}

function getSectionErrors(sectionFields = [], errors = {}) {
  let sectionErrors = []

  sectionFields.map(field => {
    if (get(errors, field.name)) {
      sectionErrors.push(get(errors, field.name))
    }

    if (field.linked) {
      sectionErrors = sectionErrors.concat(getSectionErrors(field.linked, errors))
    }
  })

  return sectionErrors
}

export {
  createFieldDefinition,
  createFieldsetDefinition,
  createFormDefinition,
  cleanErrors,
  getSectionId,
  getSectionErrors
}
