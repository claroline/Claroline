import {trans} from '#/main/app/intl/translation'

import {UserFilter} from '#/main/community/data/types/user/components/filter'
import {UsersCell} from '#/main/community/data/types/users/components/cell'
import {UsersDisplay} from '#/main/community/data/types/users/components/display'
import {UsersInput} from '#/main/community/data/types/users/components/input'

const dataType = {
  name: 'users',
  meta: {
    icon: 'fa fa-fw fa fa-user',
    label: trans('users', {}, 'data'),
    description: trans('users_desc', {}, 'data')
  },
  components: {
    details: UsersDisplay,
    input: UsersInput,
    table: UsersCell,
    search: UserFilter
  }
}

export {
  dataType
}
