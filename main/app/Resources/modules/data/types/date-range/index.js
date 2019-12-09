import isEmpty from 'lodash/isEmpty'

import {trans, tval} from '#/main/app/intl/translation'
import {chain, date, string} from '#/main/app/data/types/validators'
import {displayDate, apiDate} from '#/main/app/intl/date'

import {DateRangeInput} from '#/main/app/data/types/date-range/components/input'
import {DateRangeGroup} from '#/main/app/data/types/date-range/components/group'

// todo implements Search

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
   * @param {object} options
   *
   * @return {boolean}
   */
  validate: (value, options = {}) => {
    // it's an array of strings
    // it contains two valid dates or null
    // start < end

    return Promise.all([
      value[0] ? chain(value[0], options, [string, date]) : Promise.resolve(undefined),
      value[1] ? chain(value[1], options, [string, date]) : Promise.resolve(undefined)
    ]).then(errors => {
      if (isEmpty(errors) || (isEmpty(errors[0]) && isEmpty(errors[1]))) {
        if (value[0] && value[0] > value[1]) {
          return [null, tval('invalid_date_range')]
        }
      } else {
        return errors
      }
    })
  },

  components: {
    input: DateRangeInput,
    group: DateRangeGroup
  }
}

export {
  dataType
}
