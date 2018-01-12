import {isValidDate, localeDate, serverDate} from '#/main/core/scaffolding/date'
import {t} from '#/main/core/translation'

import {DateGroup} from '#/main/core/layout/form/components/group/date-group.jsx'
import {DateSearch} from '#/main/core/data/types/date/components/search.jsx'

const DATE_TYPE = 'date'

const dateDefinition = {
  meta: {
    type: DATE_TYPE,
    creatable: true,
    icon: 'fa fa-fw fa-calendar',
    label: t('date'),
    description: t('date_desc')
  },

  /**
   * Parses display date into ISO 8601 date.
   *
   * @param {string} display
   *
   * @return {string}
   */
  parse: (display) => display ? serverDate(display, false) : null,

  /**
   * Renders ISO date into locale date.
   *
   * @param {string} raw
   *
   * @return {string}
   */
  render: (raw) => raw ? localeDate(raw, false) : null,

  /**
   * Validates input value for a date.
   *
   * @param {string} value
   *
   * @return {boolean}
   */
  validate: (value) => typeof value === 'string' && isValidDate(value),

  components: {
    form: DateGroup,
    search: DateSearch
  }
}

export {
  DATE_TYPE,
  dateDefinition
}
