import {trans} from '#/main/app/intl/translation'
import {string} from '#/main/app/data/types/validators'

import {PasswordInput} from '#/main/app/data/types/password/components/input'

// todo handle password complexity options

const dataType = {
  name: 'password',
  meta: {
    icon: 'fa fa-fw fa-lock',
    label: trans('password', {}, 'data'),
    description: trans('password_desc', {}, 'data')
  },
  validate: (value) => string(value),
  render: () => '******',
  components: {
    input: PasswordInput
  }
}

export {
  dataType
}
