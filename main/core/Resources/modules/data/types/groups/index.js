import {trans} from '#/main/app/intl/translation'

import {GroupsDisplay} from '#/main/core/data/types/groups/components/display'
import {GroupsGroup} from '#/main/core/data/types/groups/components/group'

const dataType = {
  name: 'groups',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-users',
    label: trans('groups'),
    description: trans('groups_desc')
  },
  components: {
    details: GroupsDisplay,
    form: GroupsGroup
  }
}

export {
  dataType
}
