import {trans} from '#/main/app/intl/translation'

import {UserDisplay} from '#/main/core/data/types/user/components/display'
import {UserGroup} from '#/main/core//data/types/user/components/group'

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
    form: UserGroup
  }
}

export {
  dataType
}
