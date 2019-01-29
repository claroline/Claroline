import {trans} from '#/main/app/intl/translation'

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
