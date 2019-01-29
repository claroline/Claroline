import {trans} from '#/main/app/intl/translation'

import {UsersDisplay} from '#/main/core/data/types/users/components/display'
import {UsersInput} from '#/main/core/data/types/users/components/input'

const dataType = {
  name: 'users',
  meta: {
    icon: 'fa fa-fw fa fa-user',
    label: trans('users', {}, 'data'),
    description: trans('users_desc', {}, 'data')
  },
  components: {
    details: UsersDisplay,
    input: UsersInput
  }
}

export {
  dataType
}
