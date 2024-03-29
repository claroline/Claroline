import {trans} from '#/main/app/intl/translation'

import {passwordComplexity} from '#/main/app/data/types/password/validators'
import {PasswordInput} from '#/main/app/data/types/password/components/input'

const dataType = {
  name: 'password',
  meta: {
    icon: 'fa fa-fw fa-lock',
    label: trans('password', {}, 'data'),
    description: trans('password_desc', {}, 'data')
  },
  validate: (value, options) => {
    if (options && options.disablePasswordCheck) {
      return []
    }
    return passwordComplexity(value)
  },
  render: () => '******',
  components: {
    input: PasswordInput
  }
}

export {
  dataType
}
