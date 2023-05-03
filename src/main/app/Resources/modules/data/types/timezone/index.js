import {trans, tval} from '#/main/app/intl/translation'
import {chain, string} from '#/main/app/data/types/validators'

import {constants} from '#/main/app/intl/date/constants'
import {TimezoneInput} from '#/main/app/data/types/timezone/components/input'

const dataType = {
  name: 'timezone',
  meta: {
    icon: 'fa fa-fw fa-clock',
    label: trans('timezone', {}, 'data'),
    description: trans('timezone_desc', {}, 'data'),
    creatable: false
  },

  /**
   * Translates timezone code for display.
   */
  render: (raw) => {
    if (raw) {
      return trans(raw, {}, 'timezones')
    }

    return null
  },

  validate: (value, options) => chain(value, options, [string, (timezone) => {
    if (!constants.TIMEZONE_CODES.includes(timezone)) {
      return tval('This value should be a valid timezone.')
    }
  }]),

  components: {
    input: TimezoneInput
  }
}

export {
  dataType
}
