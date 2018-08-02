import {trans} from '#/main/core/translation'

import {DateRangeGroup} from '#/main/core/layout/form/components/group/date-range-group.jsx'

// todo implements Search
// todo implements render()
// todo implements parse()
// todo implements validate()

const dataType = {
  name: 'date-range',
  meta: {
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
  dataType
}
