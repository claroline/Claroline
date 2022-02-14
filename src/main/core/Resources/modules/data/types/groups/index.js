import {trans} from '#/main/app/intl/translation'

import {GroupsDisplay} from '#/main/core/data/types/groups/components/display'
import {GroupsInput} from '#/main/core/data/types/groups/components/input'
import {GroupsFilter} from '#/main/core/data/types/groups/components/filter'

const dataType = {
  name: 'groups',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-users',
    label: trans('groups', {}, 'data'),
    description: trans('groups_desc', {}, 'data')
  },
  render: (raw) => raw && raw.map(g => g.name).join(', '),
  components: {
    details: GroupsDisplay,
    input: GroupsInput,
    search: GroupsFilter
  }
}

export {
  dataType
}
