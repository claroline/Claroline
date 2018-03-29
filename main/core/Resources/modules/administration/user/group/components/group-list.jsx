
import {trans} from '#/main/core/translation'

import {GroupCard} from '#/main/core/user/data/components/group-card'

const GroupList = {
  open: {
    action: (row) => `#/groups/form/${row.id}`
  },
  definition: [
    {
      name: 'name',
      type: 'string',
      label: trans('name'),
      displayed: true,
      primary: true
    }
  ],
  card: GroupCard
}

export {
  GroupList
}
