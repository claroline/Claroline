import {isValidDate, displayDate, apiDate} from '#/main/core/scaffolding/date'
import {trans} from '#/main/core/translation'

import {DateGroup} from '#/main/core/layout/form/components/group/date-group.jsx'
import {DateSearch} from '#/main/core/data/types/date/components/search.jsx'

const DATE_TYPE = 'date'

const dateDefinition = {
  meta: {
    type: DATE_TYPE,
    creatable: true,
    icon: 'fa fa-fw fa-calendar',
    label: trans('date'),
    description: trans('date_desc')
  },

  /**
   * Parses display date into ISO 8601 date.
   *
   * @param {string} display
   * @param {object} options
   *
   * @return {string}
   */
  parse: (display, options = {}) => display ? apiDate(display, false, options.time) : null,

  /**
   * Renders ISO date into locale date.
   *
   * @param {string} raw
   * @param {object} options
   *
   * @return {string}
   */
  render: (raw, options = {}) => raw ? displayDate(raw, false, options.time) : null,

  /**
   * Validates input value for a date.
   *
   * @param {string} value
   * @param {object} options
   *
   * @return {boolean}
   */
  validate: (value, options = {}) => typeof value === 'string' && isValidDate(value),

  components: {
    form: DateGroup,
    search: DateSearch
  }
}

export {
  DATE_TYPE,
  dateDefinition
}
