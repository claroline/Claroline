import {trans} from '#/main/app/intl/translation'

import {UsersDisplay} from '#/main/core/data/types/users/components/display'
import {UsersGroup} from '#/main/core/data/types/users/components/group'

const dataType = {
  name: 'users',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-user',
    label: trans('users'),
    description: trans('users_desc')
  },
  components: {
    details: UsersDisplay,
    form: UsersGroup
  }
}

export {
  dataType
}
