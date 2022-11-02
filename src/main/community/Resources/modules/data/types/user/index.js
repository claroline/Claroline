import {trans} from '#/main/app/intl/translation'

import {UserDisplay} from '#/main/community/data/types/user/components/display'
import {UserCell} from '#/main/community/data/types/user/components/cell'
import {UserInput} from '#/main/community/data/types/user/components/input'
import {UserFilter} from '#/main/community/data/types/user/components/filter'

const dataType = {
  name: 'user',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-user',
    label: trans('user'),
    description: trans('user_desc')
  },
  components: {
    details: UserDisplay,
    input: UserInput,
    table: UserCell,
    search: UserFilter
  }
}

export {
  dataType
}
