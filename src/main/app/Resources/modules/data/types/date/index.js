import {trans} from '#/main/app/intl/translation'
import {chain, date, string} from '#/main/app/data/types/validators'
import {displayDate, apiDate} from '#/main/app/intl/date'

import {DateInput} from '#/main/app/data/types/date/components/input'
import {DateSearch} from '#/main/app/data/types/date/components/search'

const dataType = {
  name: 'date',
  meta: {
    creatable: true,
    icon: 'fa fa-fw fa-calendar',
    label: trans('date', {}, 'data'),
    description: trans('date_desc', {}, 'data')
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
  validate: (value, options = {}) => chain(value, options, [string, date]),

  components: {
    search: DateSearch,

    // new api
    input: DateInput
  }
}

export {
  dataType
}
