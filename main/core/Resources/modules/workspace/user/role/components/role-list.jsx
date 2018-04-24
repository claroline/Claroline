import {trans} from '#/main/core/translation'

import {RoleCard} from '#/main/core/user/data/components/role-card'

const RoleList = {
  open: (row) => ({
    type: 'link',
    target: `/roles/form/${row.id}`
  }),
  definition: [
    {
      name: 'translationKey',
      type: 'translation',
      label: trans('name'),
      displayed: true,
      primary: true
    }, {
      name: 'restrictions.maxUsers',
      type: 'number',
      label: trans('maxUsers'),
      displayed: false
    }
  ],

  card: RoleCard
}

export {
  RoleList
}
