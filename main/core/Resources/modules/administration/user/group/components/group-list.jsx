
import {t} from '#/main/core/translation'

import {GroupCard} from '#/main/core/administration/user/group/components/group-card.jsx'

const GroupList = {
  open: {
    action: (row) => `#/groups/form/${row.id}`
  },
  definition: [
    {
      name: 'name',
      type: 'string',
      label: t('name'),
      displayed: true,
      primary: true
    }
  ],
  card: GroupCard
}

export {
  GroupList
}
