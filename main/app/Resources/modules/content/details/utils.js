import get from 'lodash/get'
import merge from 'lodash/merge'

import {DataDetailsSection, DataDetailsProperty} from '#/main/app/content/details/prop-types'

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
      if (!!defaultedSection.displayed && (defaultedSection.fields || defaultedSection.component || defaultedSection.render)) {
        // adds defaults to the field configuration
        const defaultedFields = get(defaultedSection, 'fields', []).map(field => merge({}, DataDetailsProperty.defaultProps, field))
        const displayedFields = defaultedFields.filter(field => !!field.displayed)
        if (0 < displayedFields.length || defaultedSection.component || defaultedSection.render) {
          defaultedSection.fields = displayedFields

          return defaultedSection
        }
      }

      return null
    })
    .filter(section => null !== section)
}

export {
  createDetailsDefinition
}
