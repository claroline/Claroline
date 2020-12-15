import {trans} from '#/main/app/intl/translation'
import {chain, number, inRange} from '#/main/app/data/types/validators'
import {displayDuration} from '#/main/app/intl/date'

import {TimeInput} from '#/main/app/data/types/time/components/input'

const dataType = {
  name: 'time',
  meta: {
    icon: 'fa fa-fw fa-clock',
    label: trans('time', {}, 'data'),
    description: trans('time_desc', {}, 'data')
  },

  render: (value) => {
    if (value) {
      return displayDuration(value)
    }

    return '-'
  },
  validate: (value, options) => chain(value, options, [number, inRange]),
  components: {
    input: TimeInput
  }
}

export {
  dataType
}
