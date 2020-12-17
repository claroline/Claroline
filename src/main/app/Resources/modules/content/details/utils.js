import merge from 'lodash/merge'

import {DataDetailsSection, DataDetailsProperty} from '#/main/app/content/details/prop-types'

function createFieldDefinition(field) {
  const defaultedField = merge({}, DataDetailsProperty.defaultProps, field)

  // adds default to linked fields if any
  if (defaultedField.linked && 0 !== defaultedField.linked.length) {
    defaultedField.linked = createFieldsetDefinition(defaultedField.linked)
  }

  return defaultedField
}

function createFieldsetDefinition(fields) {
  return fields
    // adds default to fields
    .map(field => createFieldDefinition(field))
    // filters hidden fields
    .filter(field => !!field.displayed)
}

/**
 * Fills definition with missing default values.
 * (It excludes sections with no fields)
 *
 * @param {Array} sections
 *
 * @return {Array} - the defaulted definition
 */
function createDetailsDefinition(sections) {
  return sections
    .map(section => {
      // adds defaults to the section configuration
      const defaultedSection = merge({}, DataDetailsSection.defaultProps, section)
      if (undefined === defaultedSection.displayed || defaultedSection.displayed) {
        // section has fields and is displayed keep it
        defaultedSection.fields = createFieldsetDefinition(defaultedSection.fields)

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
