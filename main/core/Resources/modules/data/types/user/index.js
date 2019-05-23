import {trans} from '#/main/app/intl/translation'

import {UserDisplay} from '#/main/core/data/types/user/components/display'
import {UserCell} from '#/main/core//data/types/user/components/cell'
import {UserInput} from '#/main/core//data/types/user/components/input'

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
    table: UserCell
  }
}

export {
  dataType
}
