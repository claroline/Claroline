import {trans} from '#/main/app/intl/translation'

import {ip} from '#/main/app/data/types/ip/validators'
import {IpInput} from '#/main/app/data/types/ip/components/input'

// TODO : implement IP v6 input

const dataType = {
  name: 'ip',

  meta: {
    creatable: false,
    label: trans('ip', {}, 'data'),
    description: trans('ip_desc', {}, 'data')
  },

  validate: ip,

  components: {
    input: IpInput
  }
}

export {
  dataType
}
