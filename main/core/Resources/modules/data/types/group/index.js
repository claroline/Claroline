import {trans} from '#/main/app/intl/translation'

import {GroupDisplay} from '#/main/core/data/types/group/components/display'
import {GroupGroup} from '#/main/core/data/types/group/components/group'

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
    form: GroupGroup
  }
}

export {
  dataType
}
