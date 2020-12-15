import {trans} from '#/main/app/intl/translation'

import {GroupsDisplay} from '#/main/core/data/types/groups/components/display'
import {GroupsInput} from '#/main/core/data/types/groups/components/input'

const dataType = {
  name: 'groups',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-users',
    label: trans('groups', {}, 'data'),
    description: trans('groups_desc', {}, 'data')
  },
  components: {
    details: GroupsDisplay,
    input: GroupsInput
  }
}

export {
  dataType
}
