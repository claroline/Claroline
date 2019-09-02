import {trans} from '#/main/app/intl/translation'
import {displayDate, apiDate} from '#/main/app/intl/date'

import {DateRangeInput} from '#/main/app/data/types/date-range/components/input'

// todo implements Search
// todo implements render()
// todo implements parse()
// todo implements validate()

const dataType = {
  name: 'date-range',
  meta: {
    icon: 'fa fa-fw fa-calendar',
    label: trans('date_range', {}, 'actions'),
    description: trans('date_range_desc', {}, 'actions')
  },

  /**
   * Parses display date into ISO 8601 date.
   *
   * @param {string} display
   * @param {object} options
   *
   * @return {Array}
   */
  parse: (display, options = {}) => {
    let parsed = [null, null]
    if (display) {
      if (display[0]) {
        parsed[0] = apiDate(display[0], false, options.time)
      }

      if (display[1]) {
        parsed[1] = apiDate(display[1], false, options.time)
      }
    }

    return parsed
  },

  /**
   * Renders ISO date into locale date.
   *
   * @param {string} raw
   * @param {object} options
   *
   * @return {string}
   */
  render: (raw, options = {}) => {
    return `${raw[0] ? displayDate(raw[0], false, options.time) : '-'} / ${raw[1] ? displayDate(raw[1], false, options.time) : '-'}`
  },

  /**
   * Validates input value for a date range.
   *
   * @param {string} value
   *
   * @return {boolean}
   */
  validate: () => {
    // it's an array of strings
    // it contains two valid dates or null
    // start < end
  },

  components: {
    input: DateRangeInput
  }
}

export {
  dataType
}
