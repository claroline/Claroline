import {trans} from '#/main/app/intl/translation'
import {chain, date, string} from '#/main/app/data/types/validators'

import {render, parse} from '#/main/app/data/types/date/utils'
import {DateInput} from '#/main/app/data/types/date/components/input'
import {DateSearch} from '#/main/app/data/types/date/components/search'
import {DateDisplay} from '#/main/app/data/types/date/components/display'


const dataType = {
  name: 'date',
  meta: {
    creatable: true,
    icon: 'fa fa-fw fa-calendar',
    label: trans('date', {}, 'data'),
    description: trans('date_desc', {}, 'data')
  },

  parse: parse,
  render: render,

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
    input: DateInput,
    display: DateDisplay
  }
}

export {
  dataType
}
