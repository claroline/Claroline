import {trans} from '#/main/core/translation'

import {GroupCard} from '#/main/core/user/data/components/group-card'

const GroupList = {
  open: (row) => ({
    type: 'link',
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
