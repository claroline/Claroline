import {trans} from '#/main/core/translation'

import {DateRangeGroup} from '#/main/core/layout/form/components/group/date-range-group.jsx'

const DATE_RANGE_TYPE = 'date-range'

// todo implements Search
// todo implements render()
// todo implements parse()
// todo implements validate()

const dateRangeDefinition = {
  meta: {
    type: DATE_RANGE_TYPE,
    creatable: false,
    icon: 'fa fa-fw fa-calendar',
    label: trans('date_range'),
    description: trans('date_range_desc')
  },

  /**
   * Validates input value for a date range.
   *
   * @param {string} value
   *
   * @return {boolean}
   */
  validate: (value) => {
    // it's an array of strings
    // it contains two valid dates or null
    // start < end
  },

  components: {
    form: DateRangeGroup
  }
}

export {
  DATE_RANGE_TYPE,
  dateRangeDefinition
}
