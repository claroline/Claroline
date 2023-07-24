import isEmpty from 'lodash/isEmpty'

import {trans, tval} from '#/main/app/intl/translation'
import {chain, date, string} from '#/main/app/data/types/validators'

import {render, parse} from '#/main/app/data/types/date-range/utils'
import {DateRangeInput} from '#/main/app/data/types/date-range/components/input'
import {DateRangeGroup} from '#/main/app/data/types/date-range/components/group'
import {DateRangeDisplay} from '#/main/app/data/types/date-range/components/display'

// todo implements Search

const dataType = {
  name: 'date-range',
  meta: {
    icon: 'fa fa-fw fa-calendar',
    label: trans('date_range', {}, 'actions'),
    description: trans('date_range_desc', {}, 'actions')
  },

  parse: parse,

  render: render,

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
    group: DateRangeGroup,
    display: DateRangeDisplay
  }
}

export {
  dataType
}
