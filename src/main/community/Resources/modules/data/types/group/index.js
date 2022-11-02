import {trans} from '#/main/app/intl/translation'

import {GroupDisplay} from '#/main/community/data/types/group/components/display'
import {GroupInput} from '#/main/community/data/types/group/components/input'

const dataType = {
  name: 'group',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-users',
    label: trans('group'),
    description: trans('group_desc')
  },
  components: {
    details: GroupDisplay,
    input: GroupInput
  }
}

export {
  dataType
}
