import {createElement} from 'react'

import {trans} from '#/main/app/intl/translation'
import {chain, string} from '#/main/app/data/types/validators'

import {PhoneDisplay} from '#/main/app/data/types/phone/components/display'
import {PhoneInput} from '#/main/app/data/types/phone/components/input'

const dataType = {
  name: 'email',
  meta: {
    icon: 'fa fa-fw fa-phone',
    label: trans('phone', {}, 'data'),
    description: trans('phone_desc', {}, 'data')
  },
  render: (raw) => createElement('a', {href: `tel:${raw}`}, raw),
  validate: (value, options) => {
    return chain(value, options, [string])
  },
  components: {
    input: PhoneInput,
    display: PhoneDisplay
  }
}

export {
  dataType
}
