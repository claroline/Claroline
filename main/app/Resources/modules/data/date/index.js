import {trans} from '#/main/app/intl/translation'
import {isValidDate, displayDate, apiDate} from '#/main/app/intl/date'

import {DateGroup} from '#/main/core/layout/form/components/group/date-group'
import {DateSearch} from '#/main/app/data/date/components/search'

const dataType = {
  name: 'date',
  meta: {
    creatable: true,
    icon: 'fa fa-fw fa-calendar-o',
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
  dataType
}
