import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {GroupCard} from '#/main/core/user/data/components/group-card'

const GroupList = {
  open: (row) => ({
    type: LINK_BUTTON,
    target: `/groups/form/${row.id}`,
    label: trans('edit', {}, 'actions')
  }),
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
