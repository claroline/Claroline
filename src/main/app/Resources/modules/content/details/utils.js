import merge from 'lodash/merge'

import {DataDetailsSection, DataDetailsProperty} from '#/main/app/content/details/prop-types'

function isDisplayed(element, data) {
  if (undefined === element.displayed) {
    return true
  }

  return typeof element.displayed === 'function' ? element.displayed(data) : element.displayed
}

function createFieldDefinition(field, data) {
  const defaultedField = merge({}, DataDetailsProperty.defaultProps, field)

  // adds default to linked fields if any
  if (defaultedField.linked && 0 !== defaultedField.linked.length) {
    defaultedField.linked = createFieldsetDefinition(defaultedField.linked, data)
  }

  return defaultedField
}

function createFieldsetDefinition(fields, data) {
  return fields
    // adds default to fields
    .map(field => createFieldDefinition(field, data))
    // filters hidden fields
    .filter(field => isDisplayed(field, data))
}

/**
 * Fills definition with missing default values.
 * (It excludes sections with no fields)
 *
 * @param {Array} sections
 * @param {mixed} data
 *
 * @return {Array} - the defaulted definition
 */
function createDetailsDefinition(sections, data) {
  return sections
    .map(section => {
      // adds defaults to the section configuration
      const defaultedSection = merge({}, DataDetailsSection.defaultProps, section)
      if (isDisplayed(defaultedSection, data)) {
        // section has fields and is displayed keep it
        defaultedSection.fields = createFieldsetDefinition(defaultedSection.fields, data)

        if (0 !== defaultedSection.fields.length || defaultedSection.component || defaultedSection.render) {
          return defaultedSection
        }

        return null
      }

      return null
    })
    .filter(section => null !== section)
}

export {
  createDetailsDefinition
}
